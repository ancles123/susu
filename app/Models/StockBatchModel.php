<?php

namespace App\Models;

use CodeIgniter\Model;

class StockBatchModel extends Model
{
    protected $table         = 'stock_batches';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['outlet_id', 'raw_material_id', 'no_batch', 'qty_awal', 'qty_sisa', 'hpp_satuan', 'expired_date'];
    protected $useTimestamps = true;

    /** Antrian FIFO dengan FOR UPDATE — panggil HANYA di dalam transaksi DB */
    public function lockFifoQueue(int $outletId, int $rawMaterialId): array
    {
        return $this->db->query(
            'SELECT * FROM stock_batches
             WHERE outlet_id = ? AND raw_material_id = ? AND qty_sisa > 0
             ORDER BY expired_date IS NULL ASC, expired_date ASC, id ASC
             FOR UPDATE',
            [$outletId, $rawMaterialId]
        )->getResultArray();
    }

    public function getBatchesByMaterial(int $outletId, int $rawMaterialId): array
    {
        return $this->where('outlet_id', $outletId)
            ->where('raw_material_id', $rawMaterialId)
            ->where('qty_sisa >', 0)
            ->orderBy('expired_date IS NULL', 'ASC', false)
            ->orderBy('expired_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /** FR-08: batch kedaluwarsa H+2 hari */
    public function getExpiringSoon(int $outletId, int $days = 2): array
    {
        $batas = date('Y-m-d', strtotime("+{$days} days"));
        return $this->select('stock_batches.*, raw_materials.nama_item, raw_materials.satuan_kecil, outlets.nama_kios')
            ->join('raw_materials', 'raw_materials.id = stock_batches.raw_material_id')
            ->join('outlets', 'outlets.id = stock_batches.outlet_id')
            ->where('stock_batches.outlet_id', $outletId)
            ->where('stock_batches.qty_sisa >', 0)
            ->where('stock_batches.expired_date <=', $batas)
            ->orderBy('stock_batches.expired_date', 'ASC')
            ->findAll();
    }

    /** Stok menipis di bawah stok_minimum */
    public function getLowStock(int $outletId): array
    {
        return $this->db->query(
            'SELECT rm.*, COALESCE(SUM(sb.qty_sisa),0) AS stok_total
             FROM raw_materials rm
             LEFT JOIN stock_batches sb ON sb.raw_material_id = rm.id AND sb.outlet_id = ?
             GROUP BY rm.id
             HAVING stok_total <= rm.stok_minimum
             ORDER BY stok_total ASC',
            [$outletId]
        )->getResultArray();
    }

    /** HPP tertimbang aktif sebuah bahan (untuk simulasi resep) */
    public function getHppTertimbang(int $outletId, int $rawMaterialId): float
    {
        $row = $this->selectSum('(qty_sisa * hpp_satuan)', 'nilai')
            ->selectSum('qty_sisa', 'qty')
            ->where('outlet_id', $outletId)
            ->where('raw_material_id', $rawMaterialId)
            ->where('qty_sisa >', 0)
            ->first();
        $qty = (float) ($row['qty'] ?? 0);
        return $qty > 0 ? ((float) $row['nilai']) / $qty : 0.0;
    }

    public function getAllWithMaterial(int $outletId): array
    {
        return $this->select('stock_batches.*, raw_materials.nama_item, raw_materials.satuan_kecil, raw_materials.kategori')
            ->join('raw_materials', 'raw_materials.id = stock_batches.raw_material_id')
            ->where('stock_batches.outlet_id', $outletId)
            ->orderBy('stock_batches.id', 'DESC')
            ->findAll();
    }
}
