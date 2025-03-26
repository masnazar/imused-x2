<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToWarehouses extends Migration
{
    public function up()
    {
        $this->forge->addColumn('warehouses', [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at', // Letakkan setelah kolom updated_at
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('warehouses', 'deleted_at');
    }
}
