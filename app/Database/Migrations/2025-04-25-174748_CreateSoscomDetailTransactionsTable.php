<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSoscomDetailTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_id' => ['type' => 'INT', 'unsigned' => true],
            'product_id'     => ['type' => 'INT', 'unsigned' => true],
            'quantity'       => ['type' => 'INT', 'unsigned' => true],
            'hpp'            => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'unit_selling_price' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'total_hpp'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('transaction_id', 'soscom_transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('soscom_detail_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('soscom_detail_transactions');
    }
}
