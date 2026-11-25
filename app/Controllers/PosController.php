<?php

namespace App\Controllers;

use App\Models\OutletModel;
use App\Models\ProductVariantModel;
use App\Models\TransactionBatchLogModel;
use App\Models\TransactionModel;
use App\Models\UserModel;
use App\Services\CheckoutService;
use App\Services\FifoStockService;
use App\Services\ReceiptService;
use Config\Database;

class PosController extends BaseController
{
    public function index()
    {
        $shift = shift_aktif();
        if (! $shift) {
            return redirect()->to(site_url('shift/open'))->with('info', 'Buka shift dulu sebelum melayani transaksi.');
        }
        $variantModel = new ProductVariantModel();
        return view('pos/index', [
            'title'   => 'Kasir POS',
            'grouped' => $variantModel->getAktifGrouped(),
            'rasas'   => $variantModel->getDaftarRasa(),
            'shift'   => $shift,
            'outlet'  => (new OutletModel())->find($this->currentOutletId()),
        ]);
    }

    public function menuJson()
    {
        return $this->response->setJSON(['data' => (new ProductVariantModel())->where('is_active', 1)->findAll()]);
    }

    /** FR-11: Checkout FIFO atomic via CheckoutService */
    public function checkout()
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $items   = $payload['items'] ?? [];
        $metode  = $payload['metode_bayar'] ?? 'tunai';
        $uang    = (float) ($payload['uang_diterima'] ?? 0);
        $namaPlg = trim((string) ($payload['nama_pelanggan'] ?? ''));

        if (empty($items)) {
            return $this->jsonErr('Keranjang kosong.');
        }
        $shift = shift_aktif();
        if (! $shift) {
            return $this->jsonErr('Shift tidak aktif.');
        }

        $variantModel = new ProductVariantModel();
        $cart = [];
        foreach ($items as $it) {
            $vid = (int) ($it['variant_id'] ?? 0);
            $qty = (int) ($it['qty'] ?? 0);
            if ($vid <= 0 || $qty <= 0) continue;
            $variant = $variantModel->find($vid);
            if (! $variant || ! $variant['is_active']) {
                return $this->jsonErr("Menu #{$vid} tidak tersedia.");
            }
            $cart[] = ['variant_id' => $vid, 'qty' => $qty, 'variant' => $variant];
        }
        if (empty($cart)) {
            return $this->jsonErr('Item tidak valid.');
        }

        $hasil = (new CheckoutService())->process($cart, $metode, $uang, $namaPlg, $shift);
        if (! $hasil['ok']) {
            return $this->jsonErr($hasil['message']);
        }
        return $this->response->setJSON($hasil);
    }

    private function jsonErr(string $msg, int $code = 422)
    {
        return $this->response->setStatusCode($code)->setJSON(['ok' => false, 'message' => $msg]);
    }

    public function struk($id)
    {
        $trx = (new TransactionModel())->getWithDetails((int) $id);
        if (! $trx) {
            return redirect()->to(site_url('pos/riwayat'))->with('error', 'Transaksi tidak ditemukan.');
        }
        $kasir = (new UserModel())->find((int) $trx['user_id']);
        $trx['nama_kasir'] = $kasir['nama'] ?? '-';
        $outlet = (new OutletModel())->find((int) $trx['outlet_id']);
        $receipt = new ReceiptService();
        return view('pos/struk', [
            'title'     => 'Struk ' . $trx['no_invoice'],
            'trx'       => $trx,
            'outlet'    => $outlet,
            'strukText' => $receipt->strukText($trx, $outlet['nama_kios'], $outlet['alamat'] ?? ''),
            'waText'    => $receipt->waText($trx, $outlet['nama_kios']),
        ]);
    }

    public function strukData($id)
    {
        $trx = (new TransactionModel())->getWithDetails((int) $id);
        if (! $trx) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false]);
        }
        $outlet = (new OutletModel())->find((int) $trx['outlet_id']);
        $kasir  = (new UserModel())->find((int) $trx['user_id']);
        $trx['nama_kasir'] = $kasir['nama'] ?? '-';
        $receipt = new ReceiptService();
        return $this->response->setJSON([
            'ok'     => true,
            'text'   => $receipt->strukText($trx, $outlet['nama_kios'], $outlet['alamat'] ?? ''),
            'escpos' => base64_encode($receipt->strukEscPos($trx, $outlet['nama_kios'], $outlet['alamat'] ?? '')),
            'wa'     => $receipt->waText($trx, $outlet['nama_kios']),
        ]);
    }

    public function void($id)
    {
        $pin    = (string) $this->request->getPost('pin');
        $reason = trim((string) $this->request->getPost('reason'));
        $owner = (new UserModel())->verifyPinOtorisasi($pin);
        if (! $owner) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'PIN otorisasi salah.']);
        }
        $trxModel = new TransactionModel();
        $trx = $trxModel->find((int) $id);
        if (! $trx || $trx['status'] !== 'paid') {
            return $this->jsonErr('Transaksi tidak valid / sudah void.');
        }
        $db = Database::connect();
        $fifo = new FifoStockService();
        $logModel = new TransactionBatchLogModel();
        $db->transStart();
        $fifo->restoreToBatches($logModel->getLogsByTransaction((int) $id));
        $trxModel->update((int) $id, [
            'status' => 'void', 'void_at' => date('Y-m-d H:i:s'),
            'void_by' => (int) $owner['id'],
            'void_reason' => $reason !== '' ? $reason : 'Tanpa keterangan',
        ]);
        $db->transComplete();
        if (! $db->transStatus()) {
            return $this->jsonErr('Void gagal.', 500);
        }
        return $this->response->setJSON(['ok' => true, 'message' => 'Transaksi di-void oleh ' . $owner['nama'] . '. Stok dikembalikan.']);
    }

    public function riwayat()
    {
        $outletId = $this->currentOutletId();
        $shiftId  = $this->request->getGet('shift_id');
        return view('pos/riwayat', [
            'title'   => 'Riwayat Transaksi',
            'riwayat' => (new TransactionModel())->getRiwayatOutlet($outletId, 100, $shiftId ? (int) $shiftId : null),
        ]);
    }
}
