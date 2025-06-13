<?php

namespace App\Services;

use App\Models\SoscomTransactionModel;
use App\Models\SoscomDetailTransactionModel;
use App\Models\InventoryModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use CodeIgniter\Database\Exceptions\DataException;

/**
 * Service untuk handling semua logic bisnis Soscom Transactions
 */
class SoscomTransactionService
{
    protected SoscomTransactionModel $transactionModel;
    protected $soscomDetailTransactionModel; 
    protected InventoryModel $inventoryModel;
    protected ProductModel $productModel;
    protected CustomerModel $customerModel;
    protected $db;

    public function __construct()
    {
        $this->transactionModel = new SoscomTransactionModel();
        $this->soscomDetailTransactionModel = new SoscomDetailTransactionModel();
        $this->inventoryModel = new InventoryModel();
        $this->productModel = new ProductModel();
        $this->customerModel = new CustomerModel();
        $this->db = \Config\Database::connect();
    }

    /**
 * 📋 Ambil transaksi dengan pagination
 */
public function getPaginatedTransactions(array $params): array
{
    $repo = model('App\Repositories\SoscomTransactionRepository');
    $result = $repo->getPaginatedTransactions($params);

    foreach ($result['data'] as &$row) {
        $row['products'] = json_decode($row['products'] ?? '[]', true);
    }    

    return $result;
}


    /**
     * 🚀 Store transaksi manual (via form input)
     */
    public function storeTransaction(array $data)
    {
        $this->db->transStart();

        $transactionId = $this->transactionModel->insert([
            'date' => $data['date'],
            'phone_number' => $data['phone_number'],
            'customer_name' => $data['customer_name'],
            'city' => $data['city'],
            'province' => $data['province'],
            'brand_id' => $data['brand_id'],
            'total_qty' => $data['total_qty'],
            'total_omzet' => $data['total_omzet'],
            'hpp' => $data['hpp'],
            'payment_method' => $data['payment_method'],
            'cod_fee' => $data['cod_fee'],
            'shipping_cost' => $data['shipping_cost'],
            'total_payment' => $data['total_payment'],
            'estimated_profit' => $data['estimated_profit'],
            'courier_id' => $data['courier_id'],
            'tracking_number' => $data['tracking_number'],
            'team_id' => $data['team_id'] ?? null,
            'processed_by' => user_id(),
            'created_at' => date('Y-m-d H:i:s'),
            'channel' => $data['channel'] ?? 'soscom',
        ]);

        // Update / Insert Customer
        $this->upsertCustomer($data);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new DataException('Gagal menyimpan transaksi.');
        }
    }

    /**
 * 📥 Simpan hasil import Excel Soscom (Secure + Validated)
 */
public function saveImportedData(array $importedData)
{
    $this->db->transStart();

    $grouped = [];

    foreach ($importedData as $row) {
        $key = $row['phone_number'] . '||' . $row['tracking_number'];

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'date' => $row['date'],
                'phone_number' => $row['phone_number'],
                'customer_name' => $row['customer_name'],
                'city' => $row['city'],
                'province' => $row['province'],
                'brand_id' => $row['brand_id'],
                'total_qty' => 0,
                'selling_price' => 0,
                'hpp' => 0,
                'payment_method' => $row['payment_method'],
                'cod_fee' => (float) ($row['cod_fee'] ?? 0),
                'shipping_cost' => (float) ($row['shipping_cost'] ?? 0),
                'total_payment' => 0,
                'estimated_profit' => 0,
                'courier_id' => $row['courier_id'],
                'tracking_number' => $row['tracking_number'],
                'warehouse_id' => $row['warehouse_id'] ?? 1,
                'soscom_team_id' => $row['soscom_team_id'] ?? null,
                'processed_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'channel' => $row['channel'] ?? 'soscom',
                'details' => []
            ];
        }

        // Validasi stok
        $inventory = $this->inventoryModel
            ->where('product_id', $row['product_id'])
            ->where('warehouse_id', $row['warehouse_id'])
            ->first();

        if (!$inventory || $inventory['stock'] < (int)$row['quantity']) {
            throw new \RuntimeException("❌ Stok tidak mencukupi untuk Product ID {$row['product_id']} di Warehouse ID {$row['warehouse_id']}");
        }

        $quantity = (int) $row['quantity'];
        $unitHPP = (float) $row['hpp'];
        $unitPrice = (float) $row['selling_price'];
        $totalHPP = $unitHPP * $quantity;

        $grouped[$key]['total_qty'] += $quantity;
        $grouped[$key]['selling_price'] += $unitPrice;
        $grouped[$key]['hpp'] += $totalHPP;
        $grouped[$key]['total_payment'] += $unitPrice + (float)$row['cod_fee'] + (float)$row['shipping_cost'];
        $grouped[$key]['estimated_profit'] += $unitPrice - $unitHPP;

        $grouped[$key]['details'][] = [
            'product_id' => $row['product_id'],
            'quantity' => $quantity,
            'hpp' => $unitHPP,
            'total_hpp' => $totalHPP,
            'unit_selling_price' => $unitPrice,
            'tracking_number' => $row['tracking_number'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    foreach ($grouped as $transaction) {
        $details = $transaction['details'];
        unset($transaction['details']);

        if ($transaction['selling_price'] <= 0) {
            throw new \RuntimeException('❌ Harga jual tidak boleh nol.');
        }

        $this->transactionModel->insert($transaction);
        $transactionId = $this->transactionModel->getInsertID();

        foreach ($details as $detail) {
        $detail['transaction_id'] = $transactionId;
        $this->soscomDetailTransactionModel->insert($detail);

        // Update stok
        $this->updateStock($detail['product_id'], $detail['quantity'], $transaction['warehouse_id']);

        // ⬇️ Masukkan stock_transactions di sini (beneran dalam loop)
        $this->db->table('stock_transactions')->insert([
            'warehouse_id'         => $transaction['warehouse_id'],
            'product_id'           => $detail['product_id'],
            'quantity'             => $detail['quantity'],
            'transaction_type'     => 'Outbound',
            'status'               => 'Stock Out',
            'transaction_source'   => $transaction['channel'] ?? 'soscom',
            'related_warehouse_id' => $transaction['warehouse_id'],
            'reference'            => $detail['tracking_number'], // ✅ tracking number
            'created_at'           => date('Y-m-d H:i:s')
        ]);
    }


        // Update / Insert customer
        $this->upsertCustomer($transaction);
    }

    $this->db->transComplete();

    if (!$this->db->transStatus()) {
        throw new DataException('❌ Gagal menyimpan data import.');
    }
}

    /**
     * 🔁 Update stock setelah transaksi
     */
    private function updateStock(int $productId, int $quantity, int $warehouseId): void
{
    $product = $this->productModel->find($productId);
    $inventory = $this->inventoryModel
        ->where('product_id', $productId)
        ->where('warehouse_id', $warehouseId)
        ->first();

    if (!$product || !$inventory) {
        throw new \RuntimeException("❌ Stok tidak ditemukan untuk Product ID $productId di Warehouse ID $warehouseId");
    }

    // Hitung stok baru
    $newStock = max(0, $product['stock'] - $quantity);
    $totalStockValue = $newStock * $product['hpp'];

    // Update stok global
    $this->productModel->update($product['id'], [
        'stock' => $newStock,
        'hpp' => $product['hpp'],
        'total_nilai_stok' => $totalStockValue,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Update stok per warehouse
    $newLocalStock = max(0, $inventory['stock'] - $quantity);
    $this->inventoryModel->update($inventory['id'], [
        'stock' => $newLocalStock,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

    /**
     * 💾 Insert atau update customer
     */
    /**
 * 💾 Insert atau update customer dengan perhitungan LTV, days, dsb
 */
private function upsertCustomer(array $data): void
{
    $phone = $data['phone_number'];
    $payment = (float) $data['total_payment'];
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    $existing = $this->customerModel->where('phone_number', $phone)->first();

    if (!$existing) {
        // Pelanggan baru
        $this->customerModel->insert([
            'name' => $data['customer_name'],
            'phone_number' => $phone,
            'city' => $data['city'],
            'province' => $data['province'],
            'order_count' => 1,
            'ltv' => $payment,
            'first_order_date' => $data['date'],
            'last_order_date' => $data['date'],
            'days_from_last_order' => null,
            'average_days_between_orders' => null,
            'created_at' => $now,
        ]);
    } else {
        $orderCount = $existing['order_count'] + 1;
        $totalLTV = $existing['ltv'] + $payment;

        // Hitung days
        $lastOrder = new \DateTime($existing['last_order_date']);
        $newOrder = new \DateTime($data['date']);
        $daysSinceLastOrder = $lastOrder->diff($newOrder)->days;

        // Hitung average days antar order
        $firstOrder = new \DateTime($existing['first_order_date']);
        $totalDays = $firstOrder->diff($newOrder)->days;
        $avgDays = $orderCount > 1 ? round($totalDays / ($orderCount - 1)) : null;

        $this->customerModel->update($existing['id'], [
            'order_count' => $orderCount,
            'ltv' => $totalLTV,
            'last_order_date' => $data['date'],
            'days_from_last_order' => $daysSinceLastOrder,
            'average_days_between_orders' => $avgDays,
            'updated_at' => $now,
        ]);
    }
}

public function formatSoscomProducts(string $raw): array
{
    if (!$raw) return [];

    // Misalnya dipisah dengan pipe atau delimiter khusus
    $items = explode('||', $raw);
    $result = [];

    foreach ($items as $item) {
        $parts = explode('::', $item);
        if (count($parts) < 4) continue;

        [$sku, $name, $qty, $price] = $parts;

        $result[] = [
            'sku'   => $sku,
            'name'  => $name,
            'qty'   => (int)$qty,
            'price' => (int)$price
        ];
    }

    return $result;
}

    /**
     * 🚚 Track resi (placeholder)
     */
    public function trackResi(string $courier, string $awb)
    {
        // Placeholder untuk integrasi tracking resi
        return [
            'status' => 'success',
            'message' => 'Tracking updated successfully'
        ];
    }

   public function getSummaryStatistics(array $filters = []): array
{
    return model('App\Repositories\SoscomTransactionRepository')->getSummaryStats($filters);
}


}
