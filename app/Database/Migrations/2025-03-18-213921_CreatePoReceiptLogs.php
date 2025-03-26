<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePoReceiptLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'purchase_order_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'warehouse_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'received_quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'received_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('po_receipt_logs');
    }

    public function down()
    {
        $this->forge->dropTable('po_receipt_logs');
    }
}
