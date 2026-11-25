<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionDetails extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'transaction_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_variant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qty_jual'           => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'harga_jual_unit'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'hpp_unit_locked'    => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0, 'comment' => 'HPP FIFO terkunci saat transaksi'],
            'subtotal_omzet'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'subtotal_laba'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['transaction_id', 'product_variant_id'], false, 'idx_detail');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transaction_details', true);
    }

    public function down()
    {
        $this->forge->dropTable('transaction_details', true);
    }
}
