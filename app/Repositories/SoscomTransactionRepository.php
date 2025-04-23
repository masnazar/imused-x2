<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use Config\Database;

/**
 * Repository untuk query kompleks soscom_transactions
 */
class SoscomTransactionRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getDB()
    {
        return $this->db;
    }

    /**
     * 🔍 Ambil transaksi untuk keperluan DataTables
     */
    public function getBaseQuery(): BaseBuilder
    {
        return $this->db->table('soscom_transactions AS transactions')
            ->select('
                transactions.*, 
                customers.name AS customer_name,
                customers.phone_number,
                customers.city,
                customers.province,
                brands.brand_name,
                warehouses.code AS warehouse_code
            ')
            ->join('customers', 'customers.id = transactions.customer_id', 'left')
            ->join('brands', 'brands.id = transactions.brand_id', 'left')
            ->join('warehouses', 'warehouses.id = transactions.warehouse_id', 'left')
            ->where('transactions.deleted_at', null)
            ->orderBy('transactions.date', 'desc');
    }

    /**
     * 📦 Ambil produk dalam transaksi
     */
    public function getTransactionProducts(int $transactionId): array
    {
        return $this->db->table('soscom_detail_transactions AS dt')
            ->select('p.nama_produk, p.sku, dt.quantity, dt.hpp, dt.unit_selling_price')
            ->join('products AS p', 'p.id = dt.product_id')
            ->where('dt.transaction_id', $transactionId)
            ->get()
            ->getResultArray();
    }

    /**
     * 📊 Statistik Transaksi Soscom
     */
    public function getSummaryStats(array $filters): array
    {
        $builder = $this->db->table('soscom_transactions')
            ->select([
                'COUNT(id) AS total_sales',
                'SUM(selling_price) AS total_omzet',
                'SUM(hpp + discount + admin_fee + cod_fee) AS total_expenses',
                'SUM(gross_profit) AS gross_profit'
            ])
            ->where('deleted_at', null);

        if (!empty($filters['brand_id'])) {
            $builder->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $builder->where('date >=', $filters['start_date'])
                    ->where('date <=', $filters['end_date']);
        }

        return $builder->get()->getRowArray() ?? [];
    }

    /**
     * 🔁 Cek apakah customer sudah ada berdasarkan nomor WA
     */
    public function findCustomerByPhone(string $phone): ?array
    {
        return $this->db->table('customers')
            ->where('phone_number', $phone)
            ->get()
            ->getRowArray();
    }

    public function getPaginated(array $filters): array
    {
        $builder = $this->getBaseQuery();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('order_number', $filters['search'])
                ->orLike('customers.name', $filters['search'])
                ->groupEnd();
        }

        // 📅 Filter Tanggal
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $builder->where("st.date >=", $filters['start_date']);
            $builder->where("st.date <=", $filters['end_date']);
        }

        $builderFiltered = clone $builder;
        $recordsFiltered = $builderFiltered->countAllResults();

        $data = $builder
            ->limit($filters['length'], $filters['start'])
            ->get()
            ->getResultArray();

        return [
            'draw' => (int) $filters['draw'],
            'recordsTotal' => $recordsFiltered,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }
}
