<?php

namespace App\Services;

use App\Repositories\MarketplaceTransactionRepository;
use CodeIgniter\HTTP\IncomingRequest;
use App\Helpers\ProductFormatter;

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
     * Ambil statistik dashboard
     */
    // ✅ Perbaiki fungsi getStatistics di Service:
public function getStatistics(array $filters): array
{
    $platform = $filters['platform'] ?? null;
    unset($filters['platform']);

    return $this->repo->getSummaryStats($filters, $platform);
}

    /**
 * Rules validasi transaksi
 */
public function getValidationRules(): array
{
    return [
        'order_number' => 'required|string|max_length[100]',
        'date'         => 'required|valid_date',
        'brand_id'     => 'required|numeric',
        'selling_price'=> 'required|decimal',
        'hpp'          => 'required|decimal',
        // tambahkan sesuai kebutuhan
    ];
}

/**
 * Simpan transaksi baru
 */
public function createTransaction(string $platform, array $input): int
{
    $model = model('App\Models\MarketplaceTransactionModel');

    $input['platform'] = $platform;
    $input['gross_profit'] = $input['selling_price'] - ($input['hpp'] + $input['discount'] + $input['admin_fee']);
    $input['net_revenue'] = $input['selling_price'] - $input['admin_fee'];
    $input['processed_by'] = user_id(); // jika pakai auth

    $model->insert($input);
    return $model->getInsertID();
}

/**
 * Ambil produk dari transaksi
 *
 * @param int $transactionId
 * @return array
 */
public function getTransactionProducts(int $transactionId): array
{
    return $this->repo->getTransactionProducts($transactionId);
}

/**
 * Ambil detail transaksi
 */
public function getTransactionDetail(string $platform, int $id): array
{
    return $this->repo->getDB()
        ->table('marketplace_transactions mt')
        ->select('mt.*, c.courier_code, c.courier_name') // 👈 tambahin courier info
        ->join('couriers c', 'c.id = mt.courier_id', 'left') // pastikan join ke tabel courier
        ->where(['mt.id' => $id, 'mt.platform' => $platform])
        ->get()->getRowArray();
}

/**
 * Soft delete
 */
public function deleteTransaction(int $id): bool
{
    $model = model('App\Models\MarketplaceTransactionModel');
    return $model->delete($id);
}

/**
 * Import data transaksi dari file Excel
 *
 * @param string $platform
 * @param array $rows
 * @return int jumlah data berhasil di-insert
 */
public function importFromExcel(string $platform, array $rows): int
{
    $db = \Config\Database::connect();
    $builder = $db->table('marketplace_transactions');

    $inserted = 0;

    $db->transStart();

    foreach ($rows as $row) {
        [$date, $orderNumber, $trackingNumber, $storeName, $brandId, $sellingPrice, $hpp, $discount, $adminFee, $status] = $row;

        $grossProfit = (float)$sellingPrice - ((float)$hpp + (float)$discount + (float)$adminFee);
        $netRevenue = (float)$sellingPrice - (float)$adminFee;

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

public function getPaginatedTransactions(array $params)
{
    $builder = $this->repo->getBaseQuery($params['platform']);

    // ✨ Filtering
    if (!empty($params['brand_id'])) {
        $builder->where('transactions.brand_id', $params['brand_id']);
    }

    if ($params['jenis_filter'] !== 'custom' && !empty($params['periode'])) {
        [$start, $end] = $this->periodeHelper->getDateRangeFromPeriode($params['periode']);
        $builder->where('transactions.date >=', $start);
        $builder->where('transactions.date <=', $end);
    } elseif ($params['jenis_filter'] === 'custom' && !empty($params['start_date']) && !empty($params['end_date'])) {
        $builder->where('transactions.date >=', $params['start_date']);
        $builder->where('transactions.date <=', $params['end_date']);
    }

    // 🔍 Search
    if (!empty($params['search'])) {
        $builder->groupStart()
                ->like('order_number', $params['search'])
                ->orLike('tracking_number', $params['search'])
                ->groupEnd();
    }

    // 📊 Pagination
    $total = $builder->countAllResults(false);
    $data = $builder->limit($params['length'], $params['start'])->get()->getResultArray();

    // 🔁 Tambahkan detail produk & format
    foreach ($data as &$row) {
        $products = $this->repo->getTransactionProducts($row['id']);
    
        $productStrings = [];
    
        foreach ($products as $p) {
            $productStrings[] = "{$p['sku']}::{$p['nama_produk']}::{$p['quantity']}::{$p['hpp']}::{$p['unit_selling_price']}";
        }
    
        $row['products'] = $this->formatProducts(implode('||', $productStrings));
    }
    

    

    return [
        'draw'            => intval($params['draw']),
        'recordsTotal'    => $total,
        'recordsFiltered' => $total,
        'data'            => $data
    ];
}

/**
 * Format string produk menjadi HTML terstruktur
 *
 * @param string $products
 * @return string
 */
public function formatProducts(string $products): string
{
    if (empty($products)) return "-";

    $productsArray = explode('||', $products);
    $productDetails = [];

    foreach ($productsArray as $productString) {
        $parts = explode('::', $productString);
        if (count($parts) >= 5) {
            $sku = esc($parts[0]);
            $nama = esc($parts[1]);
            $qty = (int) $parts[2];
            $hpp = (float) $parts[3];
            $unitPrice = (float) $parts[4]; // unit_selling_price

            $quantity = number_format($qty, 0, ',', '.') . ' pcs';
            $price = 'Rp ' . number_format($unitPrice, 0, ',', '.');

            $productDetails[] = "<div class='d-flex align-items-center mb-2'>
                <div class='flex-grow-1'>
                    <div class='fw-medium'>{$nama}</div>
                    <small class='text-muted'>{$quantity} × {$price}</small>
                </div>
                <span class='badge bg-light text-muted border ms-2'>{$sku}</span>
            </div>";
        }
    }

    return !empty($productDetails) 
        ? implode('', $productDetails) 
        : "-";
}

}
