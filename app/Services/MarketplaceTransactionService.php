<?php

namespace App\Services;

use App\Repositories\MarketplaceTransactionRepository;

/**
 * Service Layer untuk Marketplace Transaction
 */
class MarketplaceTransactionService
{
    protected MarketplaceTransactionRepository $repo;

    public function __construct()
    {
        $this->repo = new MarketplaceTransactionRepository();
    }

    /**
     * 📊 Statistik Transaksi
     */
    public function getStatistics(array $filters): array
    {
        $platform = $filters['platform'] ?? null;
        unset($filters['platform']);

        return $this->repo->getSummaryStats($filters, $platform);
    }

    /**
     * ✅ Rules Validasi
     */
    public function getValidationRules(): array
    {
        return [
            'order_number'  => 'required|string|max_length[100]',
            'date'          => 'required|valid_date',
            'brand_id'      => 'required|numeric',
            'selling_price' => 'required|decimal',
            'hpp'           => 'required|decimal',
        ];
    }

    /**
     * 🚀 Simpan Transaksi Baru
     */
    public function createTransaction(string $platform, array $input): int
    {
        $model = model('App\Models\MarketplaceTransactionModel');

        $input['platform']      = $platform;
        $input['gross_profit']  = $input['selling_price'] - ($input['hpp'] + $input['discount'] + $input['admin_fee']);
        $input['net_revenue']   = $input['selling_price'] - $input['admin_fee'];
        $input['processed_by']  = user_id();

        $model->insert($input);
        return $model->getInsertID();
    }

    /**
     * 📦 Produk Transaksi
     */
    public function getTransactionProducts(int $transactionId): array
    {
        return $this->repo->getTransactionProducts($transactionId);
    }

    /**
     * 🔍 Detail Transaksi
     */
    public function getTransactionDetail(string $platform, int $id): array
    {
        return $this->repo->getDB()
            ->table('marketplace_transactions mt')
            ->select('mt.*, c.courier_code, c.courier_name')
            ->join('couriers c', 'c.id = mt.courier_id', 'left')
            ->where(['mt.id' => $id, 'mt.platform' => $platform])
            ->get()
            ->getRowArray();
    }

    /**
     * 🗑️ Soft Delete
     */
    public function deleteTransaction(int $id): bool
    {
        return model('App\Models\MarketplaceTransactionModel')->delete($id);
    }

    /**
     * 📥 Import dari Excel
     */
    public function importFromExcel(string $platform, array $rows): int
    {
        $db       = \Config\Database::connect();
        $builder  = $db->table('marketplace_transactions');
        $inserted = 0;

        $db->transStart();

        foreach ($rows as $row) {
            [$date, $orderNumber, $trackingNumber, $storeName, $brandId, $sellingPrice, $hpp, $discount, $adminFee, $status] = $row;

            $grossProfit = (float)$sellingPrice - ((float)$hpp + (float)$discount + (float)$adminFee);
            $netRevenue  = (float)$sellingPrice - (float)$adminFee;

            $builder->insert([
                'date'            => date('Y-m-d', strtotime($date)),
                'order_number'    => $orderNumber,
                'tracking_number' => $trackingNumber,
                'store_name'      => $storeName,
                'brand_id'        => $brandId,
                'platform'        => $platform,
                'selling_price'   => $sellingPrice,
                'hpp'             => $hpp,
                'discount'        => $discount,
                'admin_fee'       => $adminFee,
                'net_revenue'     => $netRevenue,
                'gross_profit'    => $grossProfit,
                'status'          => strtolower($status),
                'processed_by'    => user_id(),
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s')
            ]);

            $inserted++;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \Exception('Gagal import transaksi. Database rollback.');
        }

        return $inserted;
    }

    /**
     * 📄 DataTables Server-side
     */
    public function getPaginatedTransactions(array $params): array
{
    helper('periode');

    // 🧱 Inisialisasi variabel dasar
    $start = null;
    $end = null;

    // ⛳ Kalau platform = 'all' pakai method khusus
    if (strtolower($params['platform']) === 'all') {
        return $this->getPaginatedTransactionsAll($params);
    }

    // 🔨 Base builder utama
    $builder = $this->repo->getBaseQuery($params['platform']);

    // 🎯 Filter by Brand
    if (!empty($params['brand_id'])) {
        $builder->where('transactions.brand_id', $params['brand_id']);
    }

    // 🧠 Filter Periode
    if ($params['jenis_filter'] === 'periode' && !empty($params['periode'])) {
        [$start, $end] = get_date_range_from_periode($params['periode']);

        if (!empty($start) && !empty($end)) {
            $builder->where('transactions.date >=', $start)
                    ->where('transactions.date <=', $end);
            log_message('debug', '📆 Filter Periode: ' . $start . ' s.d. ' . $end);
        } else {
            log_message('warning', '[🛑 get_date_range_from_periode] hasil kosong untuk: ' . $params['periode']);
        }

    } elseif ($params['jenis_filter'] === 'custom' && !empty($params['start_date']) && !empty($params['end_date'])) {
        $builder->where('transactions.date >=', $params['start_date'])
                ->where('transactions.date <=', $params['end_date']);
        log_message('debug', '📆 Filter Custom: ' . $params['start_date'] . ' s.d. ' . $params['end_date']);
    }

    // 🔍 Filter Search
    if (!empty($params['search'])) {
        $builder->groupStart()
            ->like('order_number', $params['search'])
            ->orLike('tracking_number', $params['search'])
            ->groupEnd();
        log_message('debug', '🔎 Keyword: ' . $params['search']);
    }

    // 📊 Hitung jumlah filtered (harus di-clone setelah semua where selesai)
    $builderFiltered = clone $builder;
    $recordsFiltered = $builderFiltered->countAllResults();

    // 🔁 Ambil data dengan pagination
    $data = $builder
        ->limit((int) $params['length'], (int) $params['start'])
        ->get()
        ->getResultArray();

    // 📦 Ambil produk di setiap transaksi
    foreach ($data as &$row) {
        $products = $this->repo->getTransactionProducts($row['id']);

        $row['products'] = $products;

        $row['processed_by'] = $row['processed_by_name'] ?? '-';
    }

    return [
        'draw'            => intval($params['draw']),
        'recordsTotal'    => $recordsFiltered, // ✅ Bisa ganti ini kalau mau beda
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data
    ];
}


    private function applyDateFilter($builder, array $params)
{
    if ($params['jenis_filter'] === 'periode' && !empty($params['periode'])) {
        [$start, $end] = get_date_range_from_periode($params['periode']);
        if ($start && $end) {
            $builder->where('transactions.date >=', $start);
            $builder->where('transactions.date <=', $end);
        }
    } elseif ($params['jenis_filter'] === 'custom' && !empty($params['start_date']) && !empty($params['end_date'])) {
        $builder->where('transactions.date >=', $params['start_date']);
        $builder->where('transactions.date <=', $params['end_date']);
    }
    
    return $builder;
}
    // 📁 app/Services/MarketplaceTransactionService.php

public function getPaginatedTransactionsAll(array $params): array
{
    try {
        $builder = $this->repo->getBaseQueryAll($params);

        $builderFiltered = clone $builder;
        $recordsFiltered = $builderFiltered->countAllResults();

        $data = $builder
            ->limit((int) $params['length'], (int) $params['start'])
            ->get()
            ->getResultArray();

        foreach ($data as &$row) {
            $products = $this->repo->getTransactionProducts($row['id']);

            $row['products'] = $products;
            $row['processed_by'] = $row['processed_by_name'] ?? '-';
        }

        return [
            'draw' => intval($params['draw']),
            'recordsTotal' => $recordsFiltered,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    } catch (\Throwable $e) {
        log_message('error', '[MarketplaceTransactionService::getPaginatedTransactionsAll] ' . $e->getMessage());
        return [
            'draw' => $params['draw'] ?? 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ];
    }
}

public function getStatisticsAll(array $filters): array
{
    try {
        return $this->repo->getSummaryStats($filters, 'all');
    } catch (\Throwable $e) {
        log_message('error', '[MarketplaceTransactionService::getStatisticsAll] ' . $e->getMessage());
        return [
            'total_sales'    => 0,
            'total_omzet'    => 0,
            'total_expenses' => 0,
            'gross_profit'   => 0
        ];
    }
}


}
