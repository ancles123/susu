<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionBatchLogModel extends Model
{
    protected $table         = 'transaction_batch_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['transaction_detail_id', 'stock_batch_id', 'qty_terpotong', 'hpp_batch_unit'];
    protected $useTimestamps = false;

    /** Jejak potong batch sebuah transaksi (untuk restore saat void) */
    public function getLogsByTransaction(int $transactionId): array
    {
        return $this->db->table('transaction_batch_logs tbl')
            ->select('tbl.*, td.transaction_id')
            ->join('transaction_details td', 'td.id = tbl.transaction_detail_id')
            ->where('td.transaction_id', $transactionId)
            ->get()->getResultArray();
    }
}
