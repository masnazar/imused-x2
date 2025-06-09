<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSoscomTeamsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'team_code'  => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'team_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('soscom_teams');
    }

    public function down()
    {
        $this->forge->dropTable('soscom_teams');
    }
}
