<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantModel extends Model
{
    protected $table         = 'product_variants';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama_menu', 'varian_rasa', 'ukuran_cup', 'harga_jual', 'warna_tema', 'is_active'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'varian_rasa' => 'required',
        'ukuran_cup'  => 'required|in_list[S,M,L]',
        'harga_jual'  => 'required|numeric|greater_than[0]',
    ];

    public function getAktifGrouped(): array
    {
        $rows = $this->where('is_active', 1)
            ->orderBy('ukuran_cup', 'ASC')
            ->findAll();
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['varian_rasa']][] = $r;
        }
        return $grouped;
    }

    public function getDaftarRasa(): array
    {
        return array_keys($this->getAktifGrouped());
    }
}
