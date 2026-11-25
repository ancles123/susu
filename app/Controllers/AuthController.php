<?php

namespace App\Controllers;

use App\Models\OutletModel;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        $data['outlets'] = (new OutletModel())->where('status_aktif', 1)->findAll();
        return view('auth/login', $data);
    }

    public function attempt()
    {
        $rules = [
            'username'  => 'required',
            'password'  => 'required',
            'outlet_id' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $userModel = new UserModel();
        $user = $userModel->findByUsername((string) $this->request->getPost('username'));

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        // Tentukan outlet konteks
        $outletId = null;
        if ($user['role'] === 'kasir') {
            // Kasir terikat pada outlet akunnya (FR-01)
            $outletId = (int) $user['outlet_id'];
        } else {
            // Owner/admin: gunakan pilihan atau default ke pusat
            $pilihan = (int) $this->request->getPost('outlet_id');
            $outletId = $pilihan > 0 ? $pilihan : 1;
        }

        session()->set([
            'user_id'       => (int) $user['id'],
            'nama'          => $user['nama'],
            'username'      => $user['username'],
            'role'          => $user['role'],
            'outlet_id'     => $outletId,
            'is_logged_in'  => true,
        ]);

        // Kasir wajib punya shift terbuka sebelum POS — arahkan
        if ($user['role'] === 'kasir') {
            $shift = (new \App\Models\CashierShiftModel())->getOpenShift($outletId, (int) $user['id']);
            if (! $shift) {
                return redirect()->to(site_url('shift/open'))->with('info', 'Silakan buka shift terlebih dahulu.');
            }
            session()->set('shift_id', (int) $shift['id']);
            return redirect()->to(site_url('pos'));
        }

        return redirect()->to(site_url('dashboard'))->with('success', 'Selamat datang, ' . $user['nama'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'Anda telah keluar.');
    }

    /** AJAX: verifikasi PIN owner untuk otorisasi void (FR) */
    public function verifyPin()
    {
        $pin = (string) $this->request->getPost('pin');
        if ($pin === '') {
            return $this->response->setJSON(['ok' => false, 'message' => 'PIN wajib diisi.']);
        }
        $owner = (new UserModel())->verifyPinOtorisasi($pin);
        if (! $owner) {
            return $this->response->setJSON(['ok' => false, 'message' => 'PIN otorisasi salah atau tidak terdaftar.']);
        }
        return $this->response->setJSON(['ok' => true, 'owner_id' => (int) $owner['id'], 'owner_nama' => $owner['nama']]);
    }
}
