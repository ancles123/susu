<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTransferItemModel extends Model
{
    protected $table         = 'stock_transfer_items';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['transfer_id', 'raw_material_id', 'qty_kirim', 'qty_terima'];
    protected $useTimestamps = false;
}
