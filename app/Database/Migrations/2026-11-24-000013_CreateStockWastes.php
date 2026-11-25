<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockWastes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'outlet_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'stock_batch_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qty_rusak'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'jenis_rusak'    => ['type' => 'ENUM("basi","bocor","penyok","lainnya")', 'default' => 'basi'],
            'nominal_rugi'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'keterangan'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['outlet_id', 'created_at'], false, 'idx_waste');
        $this->forge->addForeignKey('outlet_id', 'outlets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('stock_batch_id', 'stock_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_wastes', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_wastes', true);
    }
}
