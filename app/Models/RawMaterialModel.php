<?php

namespace App\Models;

use CodeIgniter\Model;

class RawMaterialModel extends Model
{
    protected $table         = 'raw_materials';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kode_bahan', 'nama_item', 'kategori', 'satuan_kecil', 'stok_minimum'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'kode_bahan'   => 'required|max_length[20]|is_unique[raw_materials.kode_bahan,id,{id}]',
        'nama_item'    => 'required|max_length[100]',
        'kategori'     => 'required|in_list[Susu,Perasa,Pemanis,Cup,Lid,Plastik_Pendukung]',
        'satuan_kecil' => 'required|in_list[ml,gr,pcs]',
    ];

    public function getWithStok(int $outletId): array
    {
        return $this->select('raw_materials.*, COALESCE(SUM(stock_batches.qty_sisa),0) AS stok_total')
            ->join('stock_batches', "stock_batches.raw_material_id = raw_materials.id AND stock_batches.outlet_id = {$outletId}", 'left')
            ->groupBy('raw_materials.id')
            ->orderBy('raw_materials.kategori', 'ASC')
            ->orderBy('raw_materials.nama_item', 'ASC')
            ->findAll();
    }
}
