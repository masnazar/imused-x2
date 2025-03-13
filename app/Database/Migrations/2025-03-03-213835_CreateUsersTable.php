<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true, 'null' => false],
            'whatsapp'        => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => false],
            'birth_date'      => ['type' => 'DATE', 'null' => true],
            'gender'          => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'null' => false],
            'role_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'profile_image'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'bio'             => ['type' => 'TEXT', 'null' => true],
            'password'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'remember_token'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'activation_code' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
