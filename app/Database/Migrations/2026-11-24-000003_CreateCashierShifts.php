<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCashierShifts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'outlet_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'waktu_buka'        => ['type' => 'DATETIME', 'null' => true],
            'waktu_tutup'       => ['type' => 'DATETIME', 'null' => true],
            'modal_awal'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'kas_keluar'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0, 'comment' => 'Pengeluaran kas selama shift'],
            'ket_kas_keluar'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'penjualan_tunai'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'penjualan_qris'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'kas_sistem'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'kas_fisik_closing' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'selisih_kas'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'status'            => ['type' => 'ENUM("open","closed")', 'default' => 'open'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['outlet_id', 'status']);
        $this->forge->addForeignKey('outlet_id', 'outlets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cashier_shifts', true);
    }

    public function down()
    {
        $this->forge->dropTable('cashier_shifts', true);
    }
}
