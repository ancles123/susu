<?php

namespace App\Models;

use CodeIgniter\Model;

class CashierShiftModel extends Model
{
    protected $table         = 'cashier_shifts';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'outlet_id', 'user_id', 'waktu_buka', 'waktu_tutup', 'modal_awal',
        'kas_keluar', 'ket_kas_keluar', 'penjualan_tunai', 'penjualan_qris',
        'kas_sistem', 'kas_fisik_closing', 'selisih_kas', 'status',
    ];
    protected $useTimestamps = true;

    public function getOpenShift(int $outletId, int $userId): ?array
    {
        return $this->where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();
    }

    /** Hitung rekap penjualan dari transaksi (dipakai saat blind closing, di server) */
    public function hitungRekap(int $shiftId): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('transactions')
            ->selectSum("CASE WHEN metode_bayar='tunai' THEN total_omzet ELSE 0 END", 'tunai')
            ->selectSum("CASE WHEN metode_bayar='qris' THEN total_omzet ELSE 0 END", 'qris')
            ->where('shift_id', $shiftId)->where('status', 'paid')
            ->get()->getRowArray();
        return ['tunai' => (float) ($row['tunai'] ?? 0), 'qris' => (float) ($row['qris'] ?? 0)];
    }

    public function getRiwayat(int $outletId, int $limit = 20): array
    {
        return $this->select('cashier_shifts.*, users.nama AS nama_kasir')
            ->join('users', 'users.id = cashier_shifts.user_id')
            ->where('cashier_shifts.outlet_id', $outletId)
            ->orderBy('cashier_shifts.id', 'DESC')
            ->findAll($limit);
    }
}
