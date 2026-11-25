<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductVariants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_menu'    => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Misal: Susu Kambing Kurma M'],
            'varian_rasa'  => ['type' => 'ENUM("Original","Kurma","Jahe Merah","Cokelat","Stroberi","Kopi")', 'default' => 'Original'],
            'ukuran_cup'   => ['type' => 'ENUM("S","M","L")', 'default' => 'M'],
            'harga_jual'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'warna_tema'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'comment' => 'Hex warna kartu menu POS'],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['varian_rasa', 'ukuran_cup'], 'uq_rasa_ukuran');
        $this->forge->createTable('product_variants', true);
    }

    public function down()
    {
        $this->forge->dropTable('product_variants', true);
    }
}
