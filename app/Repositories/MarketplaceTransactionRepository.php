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


public function getBaseQuery(?string $platform = null): \CodeIgniter\Database\BaseBuilder
{
    $builder = $this->db->table('marketplace_transactions AS transactions')
        ->select('
            transactions.*, 
            brands.brand_name, 
            brands.primary_color,
            couriers.courier_code AS courier_code,
            u.name AS processed_by_name
        ')
        ->join('users u', 'u.id = transactions.processed_by', 'left')
        ->join('brands', 'brands.id = transactions.brand_id', 'left')
        ->join('couriers', 'couriers.id = transactions.courier_id', 'left')
        ->orderBy('transactions.date', 'desc');

    if (!empty($platform) && strtolower($platform) !== 'all') {
        $builder->where('transactions.platform', ucfirst(strtolower($platform)));
    }

    return $builder;
}




    /**
     * Hitung total statistik berdasarkan filter
     */
    public function getSummaryStats(array $filters, string $platform): array
{
    helper('periode');

    $builder = $this->getDB()->table('marketplace_transactions')
        ->select([
            'COUNT(id) AS total_sales',
            'SUM(selling_price) AS total_omzet',
            'SUM(hpp + discount + admin_fee) AS total_expenses',
            'SUM(gross_profit) AS gross_profit'
        ])
        ->where('deleted_at', null);

    // 🧠 Platform check
    if (strtolower($platform) !== 'all') {
        $builder->where('platform', $platform);
    }

    if (!empty($filters['brand_id'])) {
        $builder->where('brand_id', $filters['brand_id']);
    }

    if (($filters['jenis_filter'] ?? '') === 'periode' && !empty($filters['periode'])) {
        [$start, $end] = get_date_range_from_periode($filters['periode']);
        if ($start && $end) {
            $builder->where('date >=', $start)
                    ->where('date <=', $end);
        }
    } elseif (($filters['jenis_filter'] ?? '') === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
        $builder->where('date >=', $filters['start_date'])
                ->where('date <=', $filters['end_date']);
    }

    $result = $builder->get()->getRowArray();

    return [
        'total_sales'    => (int) ($result['total_sales'] ?? 0),
        'total_omzet'    => (float) ($result['total_omzet'] ?? 0),
        'total_expenses' => (float) ($result['total_expenses'] ?? 0),
        'gross_profit'   => (float) ($result['gross_profit'] ?? 0)
    ];
}


/**
 * Ambil detail produk berdasarkan ID transaksi
 *
 * @param int $transactionId
 * @return array
 */
public function getTransactionProducts(int $transactionId): array
{
    return $this->db->table('marketplace_detail_transaction AS dt')
        ->select('p.nama_produk, p.sku, dt.quantity, dt.hpp, dt.unit_selling_price')
        ->join('products AS p', 'p.id = dt.product_id')
        ->where('dt.transaction_id', $transactionId)
        ->get()
        ->getResultArray();
}

    public function getDB()
{
    return $this->db;
}

public function getHistoricalSales(int $productId, string $startDate, string $endDate): array
{
    $builder = $this->db->table('marketplace_detail_transaction mtd');
    $builder->select('DATE(mt.date) as tanggal, SUM(mtd.quantity) as total_qty');
    $builder->join('marketplace_transactions mt', 'mt.id = mtd.transaction_id');
    $builder->where('mtd.product_id', $productId);
    $builder->where('mt.date >=', $startDate);
    $builder->where('mt.date <=', $endDate);
    $builder->groupBy('tanggal');
    $builder->orderBy('tanggal', 'ASC');

    $result = $builder->get()->getResultArray();

    return array_map(fn($row) => (int)$row['total_qty'], $result);
}

}
