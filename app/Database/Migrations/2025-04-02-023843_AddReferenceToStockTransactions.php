<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReferenceToStockTransactions extends Migration
{
    public function up()
{
    $this->forge->addColumn('stock_transactions', [
        'reference' => [
            'type'       => 'VARCHAR',
            'constraint' => 100,
            'null'       => true,
            'after'      => 'transaction_source'
        ],
    ]);
}

public function down()
{
    $this->forge->dropColumn('stock_transactions', 'reference');
}

}
