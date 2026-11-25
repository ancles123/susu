<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['outlet_id', 'nama', 'username', 'password_hash', 'role', 'pin_otorisasi', 'is_active'];
    protected $useTimestamps = true;

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->where('is_active', 1)->first();
    }

    /** Verifikasi PIN otorisasi milik user role owner (FR: void) */
    public function verifyPinOtorisasi(string $pin): ?array
    {
        $owners = $this->where('role', 'owner')->where('pin_otorisasi IS NOT NULL', null, false)->where('is_active', 1)->findAll();
        foreach ($owners as $o) {
            if (password_verify($pin, $o['pin_otorisasi'])) {
                return $o;
            }
        }
        return null;
    }

    public function getWithOutlet(int $id): ?array
    {
        return $this->select('users.*, outlets.nama_kios')
            ->join('outlets', 'outlets.id = users.outlet_id', 'left')
            ->find($id);
    }
}
