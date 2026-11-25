<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOutlets extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_cabang'  => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_kios'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'alamat'       => ['type' => 'TEXT', 'null' => true],
            'is_pusat'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => '1 = Gudang Pusat'],
            'status_aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_cabang');
        $this->forge->createTable('outlets', true);
    }

    public function down()
    {
        $this->forge->dropTable('outlets', true);
    }
}
