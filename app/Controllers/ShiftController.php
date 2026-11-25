<?php

namespace App\Controllers;

use App\Models\CashierShiftModel;

class ShiftController extends BaseController
{
    protected CashierShiftModel $shiftModel;

    public function __construct()
    {
        $this->shiftModel = new CashierShiftModel();
    }

    public function openForm()
    {
        $outletId = $this->currentOutletId();
        $existing = $this->shiftModel->getOpenShift($outletId, (int) session()->get('user_id'));
        if ($existing) {
            session()->set('shift_id', (int) $existing['id']);
            return redirect()->to(site_url('pos'));
        }
        return view('shifts/open', ['title' => 'Buka Shift']);
    }

    public function open()
    {
        $modal = (float) str_replace(['.', ','], ['', '.'], (string) $this->request->getPost('modal_awal'));
        if ($modal < 0) {
            return redirect()->back()->with('error', 'Modal awal tidak valid.');
        }
        $outletId = $this->currentOutletId();
        $userId   = (int) session()->get('user_id');

        $existing = $this->shiftModel->getOpenShift($outletId, $userId);
        if ($existing) {
            session()->set('shift_id', (int) $existing['id']);
            return redirect()->to(site_url('pos'));
        }

        $id = $this->shiftModel->insert([
            'outlet_id'  => $outletId,
            'user_id'    => $userId,
            'waktu_buka' => date('Y-m-d H:i:s'),
            'modal_awal' => $modal,
            'status'     => 'open',
        ]);
        session()->set('shift_id', (int) $id);
        return redirect()->to(site_url('pos'))->with('success', 'Shift dibuka. Selamat bekerja!');
    }

    /** FR-03: Form Blind Closing — TIDAK menampilkan kas sistem */
    public function closeForm()
    {
        $shift = shift_aktif();
        if (! $shift) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Tidak ada shift yang terbuka.');
        }
        return view('shifts/close', [
            'title' => 'Tutup Shift (Blind Closing)',
            'shift' => $shift,
        ]);
    }

    public function close()
    {
        $shift = shift_aktif();
        if (! $shift) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Tidak ada shift yang terbuka.');
        }

        $kasFisik  = (float) str_replace(['.', ','], ['', '.'], (string) $this->request->getPost('kas_fisik_closing'));
        $kasKeluar = (float) str_replace(['.', ','], ['', '.'], (string) $this->request->getPost('kas_keluar'));
        $ketKeluar = (string) $this->request->getPost('ket_kas_keluar');

        // Hitung di SERVER setelah kasir input (blind closing)
        $rekap = $this->shiftModel->hitungRekap((int) $shift['id']);
        $kasSistem = (float) $shift['modal_awal'] + $rekap['tunai'] - $kasKeluar;
        $selisih   = $kasFisik - $kasSistem;

        $this->shiftModel->update((int) $shift['id'], [
            'waktu_tutup'       => date('Y-m-d H:i:s'),
            'penjualan_tunai'   => $rekap['tunai'],
            'penjualan_qris'    => $rekap['qris'],
            'kas_keluar'        => $kasKeluar,
            'ket_kas_keluar'    => $ketKeluar,
            'kas_sistem'        => $kasSistem,
            'kas_fisik_closing' => $kasFisik,
            'selisih_kas'       => $selisih,
            'status'            => 'closed',
        ]);
        session()->remove('shift_id');

        return redirect()->to(site_url('shift/riwayat'))->with(
            $selisih == 0 ? 'success' : 'warning',
            'Shift ditutup. Selisih kas: ' . rupiah($selisih)
        );
    }

    public function riwayat()
    {
        $outletId = $this->currentOutletId();
        return view('shifts/riwayat', [
            'title'   => 'Riwayat Shift',
            'riwayat' => $this->shiftModel->getRiwayat($outletId),
        ]);
    }
}
