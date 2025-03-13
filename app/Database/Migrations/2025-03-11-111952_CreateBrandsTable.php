<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBrandsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'supplier_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'brand_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'primary_color' => [
                'type'       => 'CHAR',
                'constraint' => 7,
            ],
            'secondary_color' => [
                'type'       => 'CHAR',
                'constraint' => 7,
            ],
            'accent_color' => [
                'type'       => 'CHAR',
                'constraint' => 7,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        // PRIMARY KEY
        $this->forge->addKey('id', true);
        
        // FOREIGN KEY ke tabel suppliers
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'CASCADE');

        // Buat tabel
        $this->forge->createTable('brands');
    }

    public function down()
    {
        $this->forge->dropTable('brands');
    }
}
