<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTransferLogModel extends Model
{
    protected $table         = 'stock_transfer_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['transfer_item_id', 'batch_asal_id', 'batch_tujuan_id', 'qty', 'hpp_satuan'];
    protected $useTimestamps = false;

    public function getLogsByItem(int $itemId): array
    {
        return $this->select('stock_transfer_logs.*, sb.no_batch')
            ->join('stock_batches sb', 'sb.id = stock_transfer_logs.batch_asal_id')
            ->where('transfer_item_id', $itemId)
            ->findAll();
    }
}
