<?php

namespace App\Models;

use CodeIgniter\Model;

class OutletModel extends Model
{
    protected $table         = 'outlets';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kode_cabang', 'nama_kios', 'alamat', 'is_pusat', 'status_aktif'];
    protected $useTimestamps = true;

    public function getPusat(): ?array
    {
        return $this->where('is_pusat', 1)->first();
    }

    public function getKiosAktif(): array
    {
        return $this->where('is_pusat', 0)->where('status_aktif', 1)->findAll();
    }
}
