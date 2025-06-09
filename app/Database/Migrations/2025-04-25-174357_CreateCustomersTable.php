<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'                 => ['type' => 'VARCHAR', 'constraint' => 255],
            'phone_number'         => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'city'                 => ['type' => 'VARCHAR', 'constraint' => 100],
            'province'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'order_count'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'ltv'                  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'first_order_date'     => ['type' => 'DATE', 'null' => true],
            'last_order_date'      => ['type' => 'DATE', 'null' => true],
            'average_days_between_orders' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
            'days_from_last_order' => ['type' => 'INT', 'null' => true],
            'segment'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('customers');
    }

    public function down()
    {
        $this->forge->dropTable('customers');
    }
}
