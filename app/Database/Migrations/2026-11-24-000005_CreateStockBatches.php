<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockBatches extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'outlet_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'raw_material_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'no_batch'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'qty_awal'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'qty_sisa'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'hpp_satuan'      => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
            'expired_date'    => ['type' => 'DATE', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Index komposit untuk antrian FIFO (FR-11)
        $this->forge->addKey(['outlet_id', 'raw_material_id', 'expired_date', 'id'], false, 'idx_fifo');
        $this->forge->addForeignKey('outlet_id', 'outlets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('raw_material_id', 'raw_materials', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_batches', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_batches', true);
    }
}
