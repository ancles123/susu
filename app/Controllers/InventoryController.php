<?php

namespace App\Controllers;

use App\Models\OutletModel;
use App\Models\RawMaterialModel;
use App\Models\StockBatchModel;
use App\Models\StockTransferItemModel;
use App\Models\StockTransferLogModel;
use App\Models\StockTransferModel;
use App\Services\FifoStockService;
use Config\Database;

class InventoryController extends BaseController
{
    public function index()
    {
        return redirect()->to(site_url('inventory/stok'));
    }

    /** Stok per outlet */
    public function stok()
    {
        $outletId = $this->currentOutletId();
        return view('inventory/stok', [
            'title' => 'Stok Bahan',
            'bahan' => (new RawMaterialModel())->getWithStok($outletId),
            'outlet' => (new OutletModel())->find($outletId),
        ]);
    }

    /** Form penerimaan bahan di pusat (buat batch baru) */
    public function penerimaanForm()
    {
        return view('inventory/penerimaan', [
            'title' => 'Penerimaan Bahan',
            'bahan' => (new RawMaterialModel())->orderBy('nama_item')->findAll(),
        ]);
    }

    public function penerimaan()
    {
        $rawMaterialId = (int) $this->request->getPost('raw_material_id');
        $qty  = (float) $this->request->getPost('qty');
        $hpp  = (float) $this->request->getPost('hpp_satuan');
        $exp  = $this->request->getPost('expired_date');
        $outletId = $this->currentOutletId();

        if ($rawMaterialId <= 0 || $qty <= 0 || $hpp < 0) {
            return redirect()->back()->withInput()->with('error', 'Data penerimaan tidak valid.');
        }
        $batch = new StockBatchModel();
        $batch->insert([
            'outlet_id' => $outletId, 'raw_material_id' => $rawMaterialId,
            'no_batch' => 'B-' . date('Ymd') . '-' . random_int(100, 999),
            'qty_awal' => $qty, 'qty_sisa' => $qty, 'hpp_satuan' => $hpp,
            'expired_date' => $exp ?: null,
        ]);
        return redirect()->to(site_url('inventory/stok'))->with('success', 'Batch stok baru ditambahkan.');
    }

    public function bahan()
    {
        return view('inventory/bahan', [
            'title' => 'Master Bahan',
            'bahan' => (new RawMaterialModel())->orderBy('kategori')->findAll(),
        ]);
    }

    public function bahanSimpan()
    {
        $model = new RawMaterialModel();
        $id = (int) $this->request->getPost('id');
        $data = [
            'kode_bahan' => strtoupper(trim((string) $this->request->getPost('kode_bahan'))),
            'nama_item' => trim((string) $this->request->getPost('nama_item')),
            'kategori' => $this->request->getPost('kategori'),
            'satuan_kecil' => $this->request->getPost('satuan_kecil'),
            'stok_minimum' => (float) $this->request->getPost('stok_minimum'),
        ];
        if ($id > 0) { $data['id'] = $id; }
        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }
        return redirect()->to(site_url('inventory/bahan'))->with('success', 'Bahan disimpan.');
    }

    public function bahanHapus($id)
    {
        (new RawMaterialModel())->delete((int) $id);
        return redirect()->to(site_url('inventory/bahan'))->with('success', 'Bahan dihapus.');
    }

    // ─── TRANSFER / DISTRIBUSI (FR-09) ───
    public function transfer()
    {
        $outletId = $this->currentOutletId();
        return view('inventory/transfer', [
            'title' => 'Distribusi Stok',
            'daftar' => (new StockTransferModel())->getDaftar($outletId, 'dari'),
        ]);
    }

    public function transferForm()
    {
        return view('inventory/transfer_form', [
            'title' => 'Buat Surat Jalan',
            'kios' => (new OutletModel())->getKiosAktif(),
            'bahan' => (new RawMaterialModel())->orderBy('nama_item')->findAll(),
        ]);
    }

    public function transferSimpan()
    {
        $keOutlet = (int) $this->request->getPost('ke_outlet_id');
        $items = $this->request->getPost('items') ?? [];
        $outletId = $this->currentOutletId();
        $userId = (int) session()->get('user_id');

        $valid = array_filter($items, static fn($i) => (int) ($i['raw_material_id'] ?? 0) > 0 && (float) ($i['qty'] ?? 0) > 0);
        if ($keOutlet <= 0 || empty($valid)) {
            return redirect()->back()->withInput()->with('error', 'Tujuan & minimal 1 item wajib diisi.');
        }
        $db = Database::connect();
        $db->transStart();
        $noSJ = (new StockTransferModel())->genNoSJ();
        $transferId = (new StockTransferModel())->insert([
            'no_surat_jalan' => $noSJ, 'dari_outlet_id' => $outletId, 'ke_outlet_id' => $keOutlet,
            'status' => 'draft', 'dibuat_oleh' => $userId,
        ]);
        $itemModel = new StockTransferItemModel();
        foreach ($valid as $i) {
            $itemModel->insert(['transfer_id' => $transferId, 'raw_material_id' => (int) $i['raw_material_id'], 'qty_kirim' => (float) $i['qty']]);
        }
        $db->transComplete();
        return redirect()->to(site_url('inventory/transfer/detail/' . $transferId))->with('success', "Surat jalan {$noSJ} dibuat (draft).");
    }

    public function transferDetail($id)
    {
        $transfer = (new StockTransferModel())->getWithRelations((int) $id);
        if (! $transfer) {
            return redirect()->to(site_url('inventory/transfer'))->with('error', 'Surat jalan tidak ditemukan.');
        }
        return view('inventory/transfer_detail', ['title' => 'Detail ' . $transfer['no_surat_jalan'], 't' => $transfer]);
    }

    /** Kirim: potong stok pusat secara FIFO, buat log batch asal (belum masuk kios) */
    public function transferKirim($id)
    {
        $transferModel = new StockTransferModel();
        $t = $transferModel->getWithRelations((int) $id);
        if (! $t || $t['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }
        $outletId = (int) $t['dari_outlet_id'];
        $fifo = new FifoStockService();
        $db = Database::connect();
        $logModel = new StockTransferLogModel();

        $db->transStart();
        foreach ($t['items'] as $item) {
            $hasil = $fifo->deductFifo($outletId, (int) $item['raw_material_id'], (float) $item['qty_kirim']);
            if (! $hasil['ok']) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Stok pusat kurang: ' . $hasil['nama_bahan']);
            }
            foreach ($hasil['alokasi'] as $alok) {
                $logModel->insert([
                    'transfer_item_id' => (int) $item['id'],
                    'batch_asal_id' => $alok['batch_id'], 'qty' => $alok['qty'], 'hpp_satuan' => $alok['hpp'],
                ]);
            }
        }
        $transferModel->update((int) $id, ['status' => 'dikirim', 'dikirim_at' => date('Y-m-d H:i:s')]);
        $db->transComplete();
        return redirect()->back()->with('success', 'Barang dikirim. Menunggu kios menerima.');
    }

    /** Batal: kembalikan stok ke batch asal jika masih draft/dikirim */
    public function transferBatal($id)
    {
        $transferModel = new StockTransferModel();
        $t = $transferModel->getWithRelations((int) $id);
        if (! $t || $t['status'] === 'diterima') {
            return redirect()->back()->with('error', 'Tidak bisa dibatalkan.');
        }
        $db = Database::connect();
        $fifo = new FifoStockService();
        $db->transStart();
        if ($t['status'] === 'dikirim') {
            $logs = $db->table('stock_transfer_logs stl')
                ->select('stl.*')->join('stock_transfer_items sti', 'sti.id = stl.transfer_item_id')
                ->where('sti.transfer_id', (int) $id)->get()->getResultArray();
            foreach ($logs as $log) {
                $fifo->restoreToBatches([['qty_terpotong' => $log['qty'], 'stock_batch_id' => $log['batch_asal_id']]]);
            }
        }
        $transferModel->update((int) $id, ['status' => 'batal']);
        $db->transComplete();
        return redirect()->back()->with('success', 'Surat jalan dibatalkan.');
    }

    // ─── TERIMA BARANG (kios) ───
    public function terimaList()
    {
        $outletId = $this->currentOutletId();
        $model = new StockTransferModel();
        return view('inventory/terima', [
            'title' => 'Terima Barang',
            'masuk' => $model->getDaftar($outletId, 'ke'),
        ]);
    }

    /** Kios klik terima: buat batch tujuan baru dari log batch asal (menjaga FIFO HPP) */
    public function terimaProses($id)
    {
        $transferModel = new StockTransferModel();
        $t = $transferModel->getWithRelations((int) $id);
        $outletId = $this->currentOutletId();
        if (! $t || $t['status'] !== 'dikirim' || (int) $t['ke_outlet_id'] !== $outletId) {
            return redirect()->back()->with('error', 'Surat jalan tidak valid.');
        }
        $db = Database::connect();
        $batchModel = new StockBatchModel();
        $db->transStart();
        foreach ($t['items'] as $item) {
            $qtyTerima = (float) $item['qty_kirim'];
            $logs = (new StockTransferLogModel())->getLogsByItem((int) $item['id']);
            foreach ($logs as $log) {
                $asal = $batchModel->find((int) $log['batch_asal_id']);
                if (! $asal) continue;
                // Buat batch tujuan dgn no_batch turunan + hpp sama (menjaga FIFO per outlet)
                $batchTujuanId = $batchModel->insert([
                    'outlet_id' => $outletId, 'raw_material_id' => (int) $item['raw_material_id'],
                    'no_batch' => $asal['no_batch'] . '-T' . $id,
                    'qty_awal' => (float) $log['qty'], 'qty_sisa' => (float) $log['qty'],
                    'hpp_satuan' => (float) $log['hpp_satuan'],
                    'expired_date' => $asal['expired_date'],
                ]);
                $db->table('stock_transfer_logs')->where('id', (int) $log['id'])
                    ->update(['batch_tujuan_id' => (int) $batchTujuanId]);
            }
            $db->table('stock_transfer_items')->where('id', (int) $item['id'])
                ->update(['qty_terima' => $qtyTerima]);
        }
        $transferModel->update((int) $id, [
            'status' => 'diterima', 'diterima_oleh' => (int) session()->get('user_id'), 'diterima_at' => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();
        return redirect()->back()->with('success', 'Barang diterima & masuk stok kios.');
    }
}
