<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTransferModel extends Model
{
    protected $table         = 'stock_transfers';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'no_surat_jalan', 'dari_outlet_id', 'ke_outlet_id', 'status', 'catatan',
        'dibuat_oleh', 'dikirim_at', 'diterima_oleh', 'diterima_at',
    ];
    protected $useTimestamps = true;

    public function genNoSJ(): string
    {
        $prefix = 'SJ-' . date('Ymd') . '-';
        $last = $this->like('no_surat_jalan', $prefix, 'after')->orderBy('id', 'DESC')->first();
        $seq = $last ? ((int) substr($last['no_surat_jalan'], -4)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function getWithRelations(int $id): ?array
    {
        $row = $this->select('stock_transfers.*, o1.nama_kios AS dari_nama, o2.nama_kios AS ke_nama, u.nama AS dibuat_oleh_nama')
            ->join('outlets o1', 'o1.id = stock_transfers.dari_outlet_id')
            ->join('outlets o2', 'o2.id = stock_transfers.ke_outlet_id')
            ->join('users u', 'u.id = stock_transfers.dibuat_oleh')
            ->find($id);
        if (! $row) return null;
        $row['items'] = $this->db->table('stock_transfer_items sti')
            ->select('sti.*, rm.nama_item, rm.satuan_kecil')
            ->join('raw_materials rm', 'rm.id = sti.raw_material_id')
            ->where('sti.transfer_id', $id)
            ->get()->getResultArray();
        return $row;
    }

    public function getDaftar(?int $outletId = null, string $arah = 'ke'): array
    {
        $b = $this->select('stock_transfers.*, o1.nama_kios AS dari_nama, o2.nama_kios AS ke_nama')
            ->join('outlets o1', 'o1.id = stock_transfers.dari_outlet_id')
            ->join('outlets o2', 'o2.id = stock_transfers.ke_outlet_id')
            ->orderBy('stock_transfers.id', 'DESC');
        if ($outletId) {
            $kolom = $arah === 'ke' ? 'ke_outlet_id' : 'dari_outlet_id';
            $b->where("stock_transfers.{$kolom}", $outletId);
        }
        return $b->findAll(100);
    }

    public function countMasukPending(int $outletId): int
    {
        return $this->where('ke_outlet_id', $outletId)->where('status', 'dikirim')->countAllResults();
    }
}
