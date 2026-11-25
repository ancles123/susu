<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'outlet_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'shift_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'no_invoice'    => ['type' => 'VARCHAR', 'constraint' => 30],
            'nama_pelanggan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'metode_bayar'  => ['type' => 'ENUM("tunai","qris")', 'default' => 'tunai'],
            'uang_diterima' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'uang_kembali'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_omzet'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_hpp'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'laba_kotor'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'status'        => ['type' => 'ENUM("paid","void")', 'default' => 'paid'],
            'void_at'       => ['type' => 'DATETIME', 'null' => true],
            'void_by'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'User ID pemberi PIN otorisasi'],
            'void_reason'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('no_invoice');
        $this->forge->addKey(['outlet_id', 'created_at'], false, 'idx_trx_outlet_tgl');
        $this->forge->addKey('shift_id');
        $this->forge->addForeignKey('outlet_id', 'outlets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('shift_id', 'cashier_shifts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('transactions', true);
    }
}
