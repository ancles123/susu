<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductRecipes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'product_variant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'raw_material_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qty_needed'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'waste_pct'          => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'comment' => 'Toleransi susut (%)'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['product_variant_id', 'raw_material_id'], 'uq_resep');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('raw_material_id', 'raw_materials', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_recipes', true);
    }

    public function down()
    {
        $this->forge->dropTable('product_recipes', true);
    }
}
