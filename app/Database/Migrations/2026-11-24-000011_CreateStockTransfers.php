<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockTransfers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'no_surat_jalan'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'dari_outlet_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'Gudang pusat'],
            'ke_outlet_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'Kios cabang'],
            'status'           => ['type' => 'ENUM("draft","dikirim","diterima","batal")', 'default' => 'draft'],
            'catatan'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'dibuat_oleh'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'dikirim_at'       => ['type' => 'DATETIME', 'null' => true],
            'diterima_oleh'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'diterima_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('no_surat_jalan');
        $this->forge->addForeignKey('dari_outlet_id', 'outlets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('ke_outlet_id', 'outlets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_transfers', true);

        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'transfer_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'raw_material_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qty_kirim'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'qty_terima'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transfer_id');
        $this->forge->addForeignKey('transfer_id', 'stock_transfers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('raw_material_id', 'raw_materials', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_transfer_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_transfer_items', true);
        $this->forge->dropTable('stock_transfers', true);
    }
}
