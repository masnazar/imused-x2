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
     * 🧹 Sanitize incoming input
     */
    public function sanitizeInput(array $input, string $platform): array
    {
        $allowed = [
            'order_number',
            'date',
            'brand_id',
            'selling_price',
            'hpp',
            'discount',
            'admin_fee',
            'tracking_number',
            'store_name',
            'status',
        ];

        $sanitized = [];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $value = $input[$field];

            switch ($field) {
                case 'date':
                    $sanitized[$field] = date('Y-m-d', strtotime((string) $value));
                    break;
                case 'brand_id':
                    $sanitized[$field] = (int) $value;
                    break;
                case 'selling_price':
                case 'hpp':
                case 'discount':
                case 'admin_fee':
                    $sanitized[$field] = (float) $value;
                    break;
                default:
                    $sanitized[$field] = is_string($value) ? esc($value) : $value;
            }
        }

        // Default numeric values
        $sanitized['discount']  = $sanitized['discount'] ?? 0.0;
        $sanitized['admin_fee'] = $sanitized['admin_fee'] ?? 0.0;

        return $sanitized;
    }

    /**
     * ✅ Rules Validasi
     */
    public function getValidationRules(string $platform): array
    {
        $rules = [
            'order_number'  => 'required|string|max_length[100]',
            'date'          => 'required|valid_date',
            'brand_id'      => 'required|numeric',
            'selling_price' => 'required|decimal',
            'hpp'           => 'required|decimal',
            'discount'      => 'permit_empty|decimal',
            'admin_fee'     => 'permit_empty|decimal',
        ];

        // Saat ini semua platform menggunakan rules yang sama
        return $rules;
    }

    /**
     * 🔒 Metadata aman untuk logging
     */
    public function getSafeMetadata(array $input): array
    {
        $fields = [
            'order_number',
            'brand_id',
            'selling_price',
            'hpp',
            'discount',
            'admin_fee',
        ];

        $metadata = [];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $value = $input[$field];
            $metadata[$field] = is_string($value) ? esc($value) : $value;
        }

        return $metadata;
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

    // 🔨 Base builder utama (platform dapat berupa nama platform atau 'all')
    $builder = $this->repo->getBaseQuery($params['platform']);
    $builderTotal = $this->repo->getBaseQuery($params['platform']);

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

    // 📊 Hitung total tanpa filter pencarian
    $recordsTotal = $builderTotal->countAllResults();

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

        $productStrings = array_map(function ($p) {
            return "{$p['sku']}::{$p['nama_produk']}::{$p['quantity']}::{$p['hpp']}::{$p['unit_selling_price']}";
        }, $products);

        $row['products'] = $this->formatProducts(implode('||', $productStrings));
        
        $row['processed_by'] = $row['processed_by_name'] ?? '-';
    }

    return [
        'draw'            => intval($params['draw']),
        'recordsTotal'    => $recordsTotal,
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

    /**
     * 🧩 Format Produk ke HTML
     */
    public function formatProducts(string $products): string
    {
        if (empty($products)) return "-";

        $productsArray = explode('||', $products);
        $productDetails = [];

        foreach ($productsArray as $productString) {
            $parts = explode('::', $productString);
            if (count($parts) >= 5) {
                [$sku, $nama, $qty, , $unitPrice] = $parts;
                $sku        = esc($sku);
                $nama       = esc($nama);
                $qty        = (int) $qty;
                $unitPrice  = (float) $unitPrice;

                $productDetails[] = "<div class='d-flex align-items-center mb-2'>
                    <div class='flex-grow-1'>
                        <div class='fw-medium'>{$nama}</div>
                        <small class='text-muted'>" . number_format($qty, 0, ',', '.') . " pcs × Rp " . number_format($unitPrice, 0, ',', '.') . "</small>
                    </div>
                    <span class='badge bg-light text-muted border ms-2'>{$sku}</span>
                </div>";
            }
        }

        return implode('', $productDetails);
    }

    /**
     * 🔄 Update status resi transaksi
     */
    public function updateResiStatus(int $id, string $status): bool
    {
        $model = model('App\\Models\\MarketplaceTransactionModel');
        return $model->update($id, ['status' => $status]);
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
