<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use Config\Database;

/**
 * Repository untuk data transaksi marketplace dari DB
 */
class MarketplaceTransactionRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getTransactions(array $filters): array
    {
        $builder = $this->db->table('marketplace_transactions mt')
            ->select('
                mt.id, mt.date, mt.order_number, mt.store_name, mt.platform, mt.status,
                mt.selling_price, mt.hpp, mt.discount, mt.admin_fee,
                (mt.selling_price - (mt.hpp + mt.discount + mt.admin_fee)) AS gross_profit,
                c.courier_name
            ')
            ->join('couriers c', 'c.id = mt.courier_id', 'left');

        // Filter tanggal
        if ($filters['filter_type'] === 'period' && $filters['month'] && $filters['year']) {
            $month = (int)$filters['month'];
            $year = (int)$filters['year'];
            $start = $month === 1 ? date("Y-m-d", strtotime(($year - 1) . "-12-25")) : date("Y-m-d", strtotime("$year-" . ($month - 1) . "-25"));
            $end = date("Y-m-d", strtotime("$year-$month-24"));
            $builder->where('mt.date >=', $start)->where('mt.date <=', $end);
        } elseif ($filters['filter_type'] === 'custom' && $filters['start_date'] && $filters['end_date']) {
            $builder->where('mt.date >=', $filters['start_date'])->where('mt.date <=', $filters['end_date']);
        }

        // Filter pencarian
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('mt.order_number', $filters['search'])
                ->orLike('mt.store_name', $filters['search'])
                ->orLike('mt.platform', $filters['search'])
                ->groupEnd();
        }

        $data = $builder->get()->getResultArray();

        return [
            'draw' => intval($_POST['draw']),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ];
    }
}
