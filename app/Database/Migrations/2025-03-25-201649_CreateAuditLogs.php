<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'model' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'model_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'action' => [
                'type' => 'ENUM',
                'constraint' => ['create', 'update', 'delete', 'restore']
            ],
            'old_data' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'new_data' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['model', 'model_id']);
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}