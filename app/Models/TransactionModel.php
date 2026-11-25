<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table         = 'transactions';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'outlet_id', 'shift_id', 'user_id', 'no_invoice', 'nama_pelanggan', 'metode_bayar',
        'uang_diterima', 'uang_kembali', 'total_omzet', 'total_hpp', 'laba_kotor',
        'status', 'void_at', 'void_by', 'void_reason',
    ];
    protected $useTimestamps = true;

    public function genNoInvoice(int $outletId): string
    {
        $tanggal = date('Ymd');
        $prefix  = "INV-{$outletId}-{$tanggal}-";
        $last = $this->like('no_invoice', $prefix, 'after')->orderBy('id', 'DESC')->first();
        $seq = $last ? ((int) substr($last['no_invoice'], -4)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function getWithDetails(int $id): ?array
    {
        $trx = $this->find($id);
        if (! $trx) return null;
        $trx['details'] = $this->db->table('transaction_details td')
            ->select('td.*, pv.nama_menu, pv.varian_rasa, pv.ukuran_cup')
            ->join('product_variants pv', 'pv.id = td.product_variant_id')
            ->where('td.transaction_id', $id)
            ->get()->getResultArray();
        return $trx;
    }

    public function getRiwayatOutlet(int $outletId, int $limit = 50, ?int $shiftId = null): array
    {
        $b = $this->select('transactions.*, users.nama AS nama_kasir')
            ->join('users', 'users.id = transactions.user_id')
            ->where('transactions.outlet_id', $outletId)
            ->orderBy('transactions.id', 'DESC');
        if ($shiftId) $b->where('transactions.shift_id', $shiftId);
        return $b->findAll($limit);
    }

    /** FR-14: Laba rugi konsolidasi per outlet dalam rentang tanggal */
    public function getLabaRugi(?string $dari, ?string $sampai, ?int $outletId = null): array
    {
        $b = $this->select('outlets.nama_kios, outlets.id AS outlet_id,
                COUNT(transactions.id) AS jml_trx,
                COALESCE(SUM(transactions.total_omzet),0) AS omzet,
                COALESCE(SUM(transactions.total_hpp),0) AS hpp,
                COALESCE(SUM(transactions.laba_kotor),0) AS laba_kotor')
            ->join('outlets', 'outlets.id = transactions.outlet_id')
            ->where('transactions.status', 'paid')
            ->groupBy('transactions.outlet_id');
        if ($dari)   $b->where('transactions.created_at >=', $dari . ' 00:00:00');
        if ($sampai) $b->where('transactions.created_at <=', $sampai . ' 23:59:59');
        if ($outletId) $b->where('transactions.outlet_id', $outletId);
        return $b->findAll();
    }

    /** Laba per varian rasa / ukuran cup */
    public function getLabaPerVarian(?string $dari, ?string $sampai, ?int $outletId = null, string $groupBy = 'pv.varian_rasa'): array
    {
        $b = $this->db->table('transaction_details td')
            ->select("{$groupBy} AS label,
                SUM(td.qty_jual) AS qty,
                SUM(td.subtotal_omzet) AS omzet,
                SUM(td.qty_jual * td.hpp_unit_locked) AS hpp,
                SUM(td.subtotal_laba) AS laba")
            ->join('transactions t', 't.id = td.transaction_id')
            ->join('product_variants pv', 'pv.id = td.product_variant_id')
            ->where('t.status', 'paid')
            ->groupBy($groupBy)
            ->orderBy('laba', 'DESC');
        if ($dari)   $b->where('t.created_at >=', $dari . ' 00:00:00');
        if ($sampai) $b->where('t.created_at <=', $sampai . ' 23:59:59');
        if ($outletId) $b->where('t.outlet_id', $outletId);
        return $b->get()->getResultArray();
    }

    /** Statistik dashboard hari ini */
    public function getStatsHarian(int $outletId): array
    {
        $row = $this->selectSum('total_omzet', 'omzet')
            ->selectSum('laba_kotor', 'laba')
            ->selectCount('id', 'trx')
            ->where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->first();
        return ['omzet' => (float) ($row['omzet'] ?? 0), 'laba' => (float) ($row['laba'] ?? 0), 'trx' => (int) ($row['trx'] ?? 0)];
    }

    /** Grafik omzet 7 hari */
    public function getGrafik7Hari(?int $outletId = null): array
    {
        $b = $this->select('DATE(created_at) AS tgl, SUM(total_omzet) AS omzet, SUM(laba_kotor) AS laba')
            ->where('status', 'paid')
            ->where('created_at >=', date('Y-m-d', strtotime('-6 days')) . ' 00:00:00')
            ->groupBy('DATE(created_at)')
            ->orderBy('tgl', 'ASC');
        if ($outletId) $b->where('outlet_id', $outletId);
        return $b->findAll();
    }
}
