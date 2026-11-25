<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionBatchLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'transaction_detail_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'stock_batch_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qty_terpotong'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'hpp_batch_unit'        => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transaction_detail_id');
        $this->forge->addForeignKey('transaction_detail_id', 'transaction_details', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('stock_batch_id', 'stock_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transaction_batch_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('transaction_batch_logs', true);
    }
}
