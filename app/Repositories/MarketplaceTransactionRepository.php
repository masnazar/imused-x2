<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use Config\Database;

/**
 * Repository untuk query kompleks marketplace_transactions
 */
class MarketplaceTransactionRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Query builder untuk DataTables server-side
     */
    public function getTransactionQuery(array $filters, string $platform): BaseBuilder
    {
        $builder = $this->db->table('marketplace_transactions mt')
            ->select("
                mt.id, mt.date, mt.order_number, mt.tracking_number, mt.store_name, mt.status,
                mt.selling_price, mt.discount, mt.admin_fee, mt.hpp,
                (mt.selling_price - (mt.discount + mt.admin_fee + mt.hpp)) as gross_profit,
                b.brand_name, b.primary_color,
                GROUP_CONCAT(DISTINCT p.nama_produk SEPARATOR ', ') as products,
                SUM(d.quantity) as total_qty
            ")
            ->join('marketplace_detail_transactions d', 'd.transaction_id = mt.id', 'left')
            ->join('products p', 'p.id = d.product_id', 'left')
            ->join('brands b', 'b.id = mt.brand_id', 'left')
            ->where('mt.platform', $platform)
            ->groupBy('mt.id');

        if (!empty($filters['brand_id'])) {
            $builder->where('mt.brand_id', $filters['brand_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $builder->where("DATE(mt.date) >=", $filters['start_date']);
            $builder->where("DATE(mt.date) <=", $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('mt.order_number', $filters['search'])
                ->orLike('mt.tracking_number', $filters['search'])
                ->orLike('mt.store_name', $filters['search'])
                ->orLike('p.nama_produk', $filters['search'])
                ->orLike('b.brand_name', $filters['search'])
                ->groupEnd();
        }

        return $builder;
    }

    /**
     * Hitung total statistik berdasarkan filter
     */
    public function getSummaryStats(array $filters, string $platform): array
    {
        $builder = $this->db->table('marketplace_transactions')
            ->select("
                COUNT(id) as total_sales,
                SUM(selling_price) as total_omzet,
                SUM(hpp + discount + admin_fee) as total_expenses,
                SUM(selling_price - (hpp + discount + admin_fee)) as gross_profit
            ")
            ->where('platform', $platform);

        if (!empty($filters['brand_id'])) {
            $builder->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $builder->where('DATE(date) >=', $filters['start_date']);
            $builder->where('DATE(date) <=', $filters['end_date']);
        }

        return $builder->get()->getRowArray();
    }
}
