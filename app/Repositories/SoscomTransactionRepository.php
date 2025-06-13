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
    /**
 * 📦 Query builder untuk DataTables Server-side (aman & clean)
 */
public function getPaginatedTransactions(array $params): array
{
    $start  = isset($params['start']) ? (int)$params['start'] : 0;
    $length = isset($params['length']) ? (int)$params['length'] : 10;
    $draw   = isset($params['draw']) ? (int)$params['draw'] : 1;

    // Daftar kolom yg boleh disort
    $columnMap = [
        0 => 'st.date',
        1 => 'st.phone_number',
        2 => 'st.customer_name',
        3 => 'st.province',
        4 => 'b.brand_name',
        5 => null, // produk (array), tidak bisa disort
        6 => 'st.total_qty',
        7 => 'st.selling_price',
        8 => 'st.hpp',
        9 => 'st.payment_method',
        10 => 'st.cod_fee',
        11 => 'st.shipping_cost',
        12 => 'st.total_payment',
        13 => 'st.tracking_number',
        14 => 'c.courier_name',
        15 => 'st.soscom_team_id',
        16 => 'u.name',
        17 => 'st.created_at',
        18 => 'st.updated_at',
    ];

    $builder = $this->db->table('soscom_transactions st')
    ->select("
    st.id, st.date, st.phone_number, st.customer_name, st.province,
    b.brand_name, b.primary_color, 
    st.total_qty, st.selling_price, st.hpp, st.payment_method,
    st.cod_fee, st.shipping_cost, st.total_payment, st.estimated_profit,
    st.tracking_number,
    c.courier_name AS courier_name,
    st.soscom_team_id,
    teams.team_name AS soscom_team_name,
    u.name AS processed_by,
    st.created_at, st.updated_at,
    st.channel,
")
->join('soscom_teams teams', 'teams.id = st.soscom_team_id', 'left')
        ->join('brands b', 'b.id = st.brand_id', 'left')
        ->join('couriers c', 'c.id = st.courier_id', 'left')
        ->join('users u', 'u.id = st.processed_by', 'left');

    // 🔍 Search
    if (!empty($params['search']['value'])) {
        $search = trim($params['search']['value']);
        $builder->groupStart()
            ->like('st.phone_number', $search)
            ->orLike('st.customer_name', $search)
            ->orLike('b.brand_name', $search)
            ->orLike('c.courier_name', $search)
            ->groupEnd();
    }

    // Tambahkan ini setelah join dan search
if (!empty($params['jenis_filter']) && $params['jenis_filter'] === 'periode') {
    if (!empty($params['periode'])) {
        helper('periode');
        [$start, $end] = get_date_range_from_periode($params['periode']);
        if ($start && $end) {
            $builder->where('st.date >=', $start)
                    ->where('st.date <=', $end);
        }
    }
} elseif (!empty($params['jenis_filter']) && $params['jenis_filter'] === 'custom') {
    if (!empty($params['start_date'])) {
        $builder->where('st.date >=', $params['start_date']);
    }
    if (!empty($params['end_date'])) {
        $builder->where('st.date <=', $params['end_date']);
    }
}


    // ⏱️ Order by (default: terbaru)
    if (!empty($params['order'][0]['column']) && isset($columnMap[$params['order'][0]['column']])) {
        $colIndex = $params['order'][0]['column'];
        $dir = $params['order'][0]['dir'] === 'asc' ? 'asc' : 'desc';
        $column = $columnMap[$colIndex];
        if ($column) {
            $builder->orderBy($column, $dir);
        }
    } else {
        $builder->orderBy('st.date', 'desc');
    }

    // 📊 Hitung total (tanpa pagination)
    $builderCount = clone $builder;
    $totalRecords = $builderCount->countAllResults();

    // 📦 Ambil data
    $builder->limit($length, $start);
    $results = $builder->get()->getResultArray();

    // Produk (join ke detail)
    foreach ($results as &$row) {
        $products = $this->db->table('soscom_detail_transactions sdt')
    ->select('p.sku, p.nama_produk AS name, sdt.quantity AS qty, sdt.unit_selling_price AS price')
    ->join('products p', 'p.id = sdt.product_id', 'left')
    ->where('sdt.transaction_id', $row['id'])
    ->get()
    ->getResultArray();

    $row['products'] = json_encode($products);
    }

    return [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $results
    ];
}


    /**
     * 📊 Hitung semua transaksi
     */
    private function countAllTransactions(): int
    {
        return (int)$this->db->table('soscom_transactions')->countAllResults();
    }

    public function getSummaryStats(array $filters = []): array
    {

        if (!empty($filters['channel'])) {
            $builder->where('channel', $filters['channel']);
        }
        $builder = $this->db->table('soscom_transactions')
            ->select([
                'COUNT(id) AS total_sales',
                'SUM(selling_price) AS total_omzet',
                'SUM(hpp) AS total_hpp',
                'SUM(estimated_profit) AS total_profit'
            ]);
    
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

    public function getTransactionsByPhone(string $phone, array $params = []): array
{
    $start  = (int)($params['start'] ?? 0);
    $length = (int)($params['length'] ?? 10);
    $draw   = (int)($params['draw'] ?? 1);

    $builder = $this->db->table('soscom_transactions st')
        ->select("
            st.id, st.date, st.channel, st.total_qty, st.total_payment,
            st.tracking_number, c.courier_name
        ")
        ->join('couriers c', 'c.id = st.courier_id', 'left')
        ->where('st.phone_number', $phone)
        ->orderBy('st.date', 'desc');

    // Hitung total
    $builderCount = clone $builder;
    $total = $builderCount->countAllResults();

    // Ambil data
    $builder->limit($length, $start);
    $results = $builder->get()->getResultArray();

    // Ambil produk per transaksi
    foreach ($results as &$row) {
        $products = $this->db->table('soscom_detail_transactions sdt')
            ->select('p.nama_produk')
            ->join('products p', 'p.id = sdt.product_id', 'left')
            ->where('sdt.transaction_id', $row['id'])
            ->get()
            ->getResultArray();

        $row['product_names'] = implode(', ', array_column($products, 'nama_produk'));
    }

    return [
        'draw' => $draw,
        'recordsTotal' => $total,
        'recordsFiltered' => $total,
        'data' => $results
    ];
}

    

    /**
     * 🔍 Ambil database instance
     */
    public function getDB()
    {
        return $this->db;
    }
}
