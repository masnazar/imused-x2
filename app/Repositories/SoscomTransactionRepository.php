<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use Config\Database;

/**
 * Repository untuk modul Soscom Transactions
 */
class SoscomTransactionRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * 📦 Query builder untuk DataTables Server-side
     */
    public function getPaginatedTransactions(array $params): array
    {
        $builder = $this->db->table('soscom_transactions AS transactions')
            ->select('
                transactions.*,
                brands.brand_name,
                couriers.courier_code,
                soscom_teams.team_name
            ')
            ->join('brands', 'brands.id = transactions.brand_id', 'left')
            ->join('couriers', 'couriers.id = transactions.courier_id', 'left')
            ->join('soscom_teams', 'soscom_teams.id = transactions.soscom_team_id', 'left');

        // 🔍 Filtering by brand
        if (!empty($params['brand_id'])) {
            $builder->where('transactions.brand_id', $params['brand_id']);
        }

        // 🔍 Filtering by date
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $builder->where('transactions.date >=', $params['start_date']);
            $builder->where('transactions.date <=', $params['end_date']);
        }

        // 🔎 Search (multi-field)
        if (!empty($filters['search']['value'])) {
            $search = $filters['search']['value'];
        
            $builder->groupStart()
                ->like('st.customer_name', $search)
                ->orLike('st.customer_whatsapp', $search)
                ->orLike('st.city', $search)
                ->orLike('st.province', $search)
                ->groupEnd();
        }
        

        // 🚀 Clone builder sebelum limit untuk get total filtered
        $builderFiltered = clone $builder;
        $recordsFiltered = $builderFiltered->countAllResults(false);

        // 🔁 Pagination
        $builder->limit((int)$params['length'], (int)$params['start']);
        $data = $builder->get()->getResultArray();

        return [
            'draw'            => intval($params['draw']),
            'recordsTotal'    => $this->countAllTransactions(),
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data
        ];
    }

    /**
     * 📊 Hitung semua transaksi
     */
    private function countAllTransactions(): int
    {
        return (int)$this->db->table('soscom_transactions')->countAllResults();
    }

    /**
     * 📈 Statistik Laporan
     */
    public function getSummaryStats(array $filters = []): array
    {
        $builder = $this->db->table('soscom_transactions')
            ->select([
                'COUNT(id) AS total_sales',
                'SUM(total_omzet) AS total_omzet',
                'SUM(hpp) AS total_hpp',
                'SUM(estimated_profit) AS total_profit'
            ]);

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $builder->where('date >=', $filters['start_date']);
            $builder->where('date <=', $filters['end_date']);
        }

        if (!empty($filters['brand_id'])) {
            $builder->where('brand_id', $filters['brand_id']);
        }

        $result = $builder->get()->getRowArray();

        return [
            'total_sales'  => (int)($result['total_sales'] ?? 0),
            'total_omzet'  => (float)($result['total_omzet'] ?? 0),
            'total_hpp'    => (float)($result['total_hpp'] ?? 0),
            'total_profit' => (float)($result['total_profit'] ?? 0),
        ];
    }

    /**
     * 📦 Produk per transaksi
     */
    public function getTransactionProducts(int $transactionId): array
    {
        return $this->db->table('soscom_transaction_details AS details')
            ->select('
                products.product_name,
                products.sku,
                details.quantity,
                details.hpp,
                details.unit_selling_price
            ')
            ->join('products', 'products.id = details.product_id', 'left')
            ->where('details.transaction_id', $transactionId)
            ->get()
            ->getResultArray();
    }

    /**
     * 🔍 Ambil database instance
     */
    public function getDB()
    {
        return $this->db;
    }
}
