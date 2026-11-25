<?php

namespace App\Controllers;

use App\Models\OutletModel;
use App\Models\UserModel;

class OwnerController extends BaseController
{
    public function users()
    {
        $userModel = new UserModel();
        return view('owner/users', [
            'title' => 'Manajemen Pengguna',
            'users' => $userModel->select('users.*, outlets.nama_kios')->join('outlets', 'outlets.id = users.outlet_id', 'left')->findAll(),
            'outlets' => (new OutletModel())->where('status_aktif', 1)->findAll(),
        ]);
    }

    public function userSimpan()
    {
        $model = new UserModel();
        $id = (int) $this->request->getPost('id');
        $data = [
            'nama' => trim((string) $this->request->getPost('nama')),
            'username' => trim((string) $this->request->getPost('username')),
            'role' => $this->request->getPost('role'),
            'outlet_id' => $this->request->getPost('outlet_id') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];
        $pass = (string) $this->request->getPost('password');
        if ($pass !== '') {
            $data['password_hash'] = password_hash($pass, PASSWORD_BCRYPT);
        }
        $pin = (string) $this->request->getPost('pin_otorisasi');
        if ($pin !== '' && $data['role'] === 'owner') {
            $data['pin_otorisasi'] = password_hash($pin, PASSWORD_BCRYPT);
        }
        if ($id > 0) { $data['id'] = $id; }
        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('error', 'Gagal simpan pengguna.');
        }
        return redirect()->to(site_url('owner/users'))->with('success', 'Pengguna disimpan.');
    }

    public function userToggle($id)
    {
        $model = new UserModel();
        $u = $model->find((int) $id);
        if ($u && (int) $u['id'] !== (int) session()->get('user_id')) {
            $model->update((int) $id, ['is_active' => $u['is_active'] ? 0 : 1]);
        }
        return redirect()->back()->with('success', 'Status pengguna diubah.');
    }

    public function outlets()
    {
        return view('owner/outlets', [
            'title' => 'Manajemen Outlet',
            'outlets' => (new OutletModel())->findAll(),
        ]);
    }

    public function outletSimpan()
    {
        $model = new OutletModel();
        $id = (int) $this->request->getPost('id');
        $data = [
            'kode_cabang' => strtoupper(trim((string) $this->request->getPost('kode_cabang'))),
            'nama_kios' => trim((string) $this->request->getPost('nama_kios')),
            'alamat' => trim((string) $this->request->getPost('alamat')),
            'is_pusat' => $this->request->getPost('is_pusat') ? 1 : 0,
            'status_aktif' => $this->request->getPost('status_aktif') ? 1 : 0,
        ];
        if ($id > 0) { $data['id'] = $id; }
        $model->save($data);
        return redirect()->to(site_url('owner/outlets'))->with('success', 'Outlet disimpan.');
    }
}
