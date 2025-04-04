<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitSellingPriceToMarketplaceDetailTransaction extends Migration
{
    public function up()
    {
        $this->forge->addColumn('marketplace_detail_transaction', [
            'unit_selling_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'after'      => 'hpp',
                'null'       => true,
                'default'    => 0.00,
                'comment'    => 'Harga jual per item'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('marketplace_detail_transaction', 'unit_selling_price');
    }
}
