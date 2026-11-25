<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailModel extends Model
{
    protected $table         = 'transaction_details';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'transaction_id', 'product_variant_id', 'qty_jual',
        'harga_jual_unit', 'hpp_unit_locked', 'subtotal_omzet', 'subtotal_laba',
    ];
    protected $useTimestamps = true;
}
