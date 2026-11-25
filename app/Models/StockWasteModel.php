<?php

namespace App\Models;

use CodeIgniter\Model;

class StockWasteModel extends Model
{
    protected $table         = 'stock_wastes';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['outlet_id', 'stock_batch_id', 'user_id', 'qty_rusak', 'jenis_rusak', 'nominal_rugi', 'keterangan'];
    protected $useTimestamps = true;

    public function getDaftar(?int $outletId = null, ?string $dari = null, ?string $sampai = null): array
    {
        $b = $this->select('stock_wastes.*, raw_materials.nama_item, raw_materials.satuan_kecil,
                stock_batches.no_batch, users.nama AS nama_user, outlets.nama_kios')
            ->join('stock_batches', 'stock_batches.id = stock_wastes.stock_batch_id')
            ->join('raw_materials', 'raw_materials.id = stock_batches.raw_material_id')
            ->join('users', 'users.id = stock_wastes.user_id')
            ->join('outlets', 'outlets.id = stock_wastes.outlet_id')
            ->orderBy('stock_wastes.id', 'DESC');
        if ($outletId) $b->where('stock_wastes.outlet_id', $outletId);
        if ($dari)     $b->where('stock_wastes.created_at >=', $dari . ' 00:00:00');
        if ($sampai)   $b->where('stock_wastes.created_at <=', $sampai . ' 23:59:59');
        return $b->findAll(200);
    }

    public function getTotalRugi(?int $outletId = null, ?string $dari = null, ?string $sampai = null): float
    {
        $b = $this->selectSum('nominal_rugi', 'rugi');
        if ($outletId) $b->where('outlet_id', $outletId);
        if ($dari)     $b->where('created_at >=', $dari . ' 00:00:00');
        if ($sampai)   $b->where('created_at <=', $sampai . ' 23:59:59');
        $row = $b->first();
        return (float) ($row['rugi'] ?? 0);
    }
}
