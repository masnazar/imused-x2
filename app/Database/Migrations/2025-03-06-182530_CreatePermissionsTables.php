<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissionsAndRolePermissionsTables extends Migration
{
    public function up()
    {
        // Buat tabel permissions dulu
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'permission_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'alias'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('permissions');

        // Buat tabel role_permissions setelah roles & permissions ada
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'role_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // 🔥 Tambahkan Unsigned
            'permission_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // 🔥 Tambahkan Unsigned
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
    }
}
