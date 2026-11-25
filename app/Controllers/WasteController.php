<?php

namespace App\Controllers;

use App\Models\RawMaterialModel;
use App\Models\StockBatchModel;
use App\Models\StockWasteModel;
use Config\Database;

class WasteController extends BaseController
{
    public function index()
    {
        $outletId = $this->currentOutletId();
        $wasteModel = new StockWasteModel();
        return view('waste/index', [
            'title' => 'Waste / Barang Rusak',
            'daftar' => $wasteModel->getDaftar($outletId),
            'totalRugi' => $wasteModel->getTotalRugi($outletId),
        ]);
    }

    public function buat()
    {
        return view('waste/buat', [
            'title' => 'Catat Waste',
            'bahan' => (new RawMaterialModel())->orderBy('nama_item')->findAll(),
        ]);
    }

    /** JSON batch berstok untuk bahan di outlet ini (untuk dropdown) */
    public function batches($rawMaterialId)
    {
        $outletId = $this->currentOutletId();
        $rows = (new StockBatchModel())->getBatchesByMaterial($outletId, (int) $rawMaterialId);
        return $this->response->setJSON(['data' => $rows]);
    }

    public function simpan()
    {
        $batchId = (int) $this->request->getPost('stock_batch_id');
        $qty     = (float) $this->request->getPost('qty_rusak');
        $jenis   = $this->request->getPost('jenis_rusak');
        $ket     = trim((string) $this->request->getPost('keterangan'));
        $outletId = $this->currentOutletId();

        $batchModel = new StockBatchModel();
        $batch = $batchModel->find($batchId);
        if (! $batch || (int) $batch['outlet_id'] !== $outletId) {
            return redirect()->back()->withInput()->with('error', 'Batch tidak valid.');
        }
        if ($qty <= 0 || $qty > (float) $batch['qty_sisa']) {
            return redirect()->back()->withInput()->with('error', 'Qty waste tidak valid (melebihi sisa batch).');
        }

        $nominalRugi = $qty * (float) $batch['hpp_satuan'];
        $db = Database::connect();
        $db->transStart();
        $batchModel->update($batchId, ['qty_sisa' => (float) $batch['qty_sisa'] - $qty]);
        (new StockWasteModel())->insert([
            'outlet_id' => $outletId, 'stock_batch_id' => $batchId,
            'user_id' => (int) session()->get('user_id'),
            'qty_rusak' => $qty, 'jenis_rusak' => $jenis,
            'nominal_rugi' => $nominalRugi, 'keterangan' => $ket,
        ]);
        $db->transComplete();
        return redirect()->to(site_url('waste'))->with('success', 'Waste dicatat. Rugi ' . rupiah($nominalRugi));
    }
}
