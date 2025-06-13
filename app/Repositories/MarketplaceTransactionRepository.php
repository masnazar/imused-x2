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
            SUM(d.quantity) as total_qty
        ")
        ->join('marketplace_detail_transaction d', 'd.transaction_id = mt.id', 'left')
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

// 📁 app/Repositories/MarketplaceTransactionRepository.php

public function getBaseQueryAll(array $params): \CodeIgniter\Database\BaseBuilder
{
    $builder = $this->db->table('marketplace_transactions')
        ->select('
            marketplace_transactions.*, 
            brands.brand_name, 
            brands.primary_color,
            users.name as processed_by_name
        ')
        ->join('brands', 'brands.id = marketplace_transactions.brand_id', 'left')
        ->join('users', 'users.id = marketplace_transactions.processed_by', 'left')
        ->orderBy('marketplace_transactions.date', 'desc');

    // Filter brand
    if (!empty($params['brand_id'])) {
        $builder->where('marketplace_transactions.brand_id', $params['brand_id']);
    }

    // Filter tanggal
    if (!empty($params['start_date']) && !empty($params['end_date'])) {
        $builder->where('marketplace_transactions.date >=', $params['start_date']);
        $builder->where('marketplace_transactions.date <=', $params['end_date']);
    }

    // Filter pencarian
    if (!empty($params['search'])) {
        $builder->groupStart()
            ->like('marketplace_transactions.order_number', $params['search'])
            ->orLike('marketplace_transactions.tracking_number', $params['search'])
            ->orLike('brands.brand_name', $params['search'])
            ->orLike('store_name', $params['search'])
            ->groupEnd();
    }

    return $builder;
}

}
