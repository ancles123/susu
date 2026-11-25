<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockTransferLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'transfer_item_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'batch_asal_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'Batch pusat yang dipotong FIFO'],
            'batch_tujuan_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'Batch baru yang dibuat di kios'],
            'qty'               => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'hpp_satuan'        => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transfer_item_id');
        $this->forge->addForeignKey('transfer_item_id', 'stock_transfer_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_transfer_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_transfer_logs', true);
    }
}
