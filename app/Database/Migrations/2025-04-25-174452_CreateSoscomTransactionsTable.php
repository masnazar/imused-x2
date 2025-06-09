<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSoscomTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'date'            => ['type' => 'DATE'],
            'phone_number'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'customer_name'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'city'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'province'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'brand_id'        => ['type' => 'INT', 'unsigned' => true],
            'total_qty'       => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'selling_price'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'hpp'             => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'payment_method'  => ['type' => 'ENUM', 'constraint' => ['COD', 'Transfer', 'Tunai']],
            'cod_fee'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'shipping_cost'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'total_payment'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'estimated_profit'=> ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'courier_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'tracking_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'shipping_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'soscom_team_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'processed_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('brand_id', 'brands', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('courier_id', 'couriers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('soscom_team_id', 'soscom_teams', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('soscom_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('soscom_transactions');
    }
}
