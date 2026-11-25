<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRawMaterials extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_bahan'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_item'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'kategori'     => ['type' => 'ENUM("Susu","Perasa","Pemanis","Cup","Lid","Plastik_Pendukung")', 'default' => 'Susu'],
            'satuan_kecil' => ['type' => 'ENUM("ml","gr","pcs")', 'default' => 'pcs'],
            'stok_minimum' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_bahan');
        $this->forge->createTable('raw_materials', true);
    }

    public function down()
    {
        $this->forge->dropTable('raw_materials', true);
    }
}
