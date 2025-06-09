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
        return model('App\Repositories\SoscomTransactionRepository')->getPaginatedTransactions($params);
    }

    /**
     * 🚀 Store transaksi manual (via form input)
     */
    public function storeTransaction(array $data)
    {
        $this->db->transStart();

        $transactionId = $this->transactionModel->insert([
            'date' => $data['date'],
            'whatsapp_number' => $data['whatsapp_number'],
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
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update / Insert Customer
        $this->upsertCustomer($data);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new DataException('Gagal menyimpan transaksi.');
        }
    }

    /**
     * 📥 Simpan hasil import Excel
     */
    public function saveImportedData(array $importedData)
    {
        $this->db->transStart();

        $grouped = [];

        foreach ($importedData as $row) {
            $key = $row['whatsapp_number'] . '||' . $row['tracking_number'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date' => $row['date'],
                    'whatsapp_number' => $row['whatsapp_number'],
                    'customer_name' => $row['customer_name'],
                    'city' => $row['city'],
                    'province' => $row['province'],
                    'brand_id' => $row['brand_id'],
                    'total_qty' => 0,
                    'total_omzet' => 0,
                    'hpp' => 0,
                    'payment_method' => $row['payment_method'],
                    'cod_fee' => (float) ($row['cod_fee'] ?? 0),
                    'shipping_cost' => (float) ($row['shipping_cost'] ?? 0),
                    'total_payment' => 0,
                    'estimated_profit' => 0,
                    'courier_id' => $row['courier_id'],
                    'tracking_number' => $row['tracking_number'],
                    'team_id' => null,
                    'processed_by' => user_id(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'details' => []
                ];
            }

            $grouped[$key]['total_qty'] += (int) $row['quantity'];
            $grouped[$key]['total_omzet'] += (float) $row['selling_price'];
            $grouped[$key]['hpp'] += (float) $row['hpp'] * (int) $row['quantity'];
            $grouped[$key]['total_payment'] += (float) $row['selling_price'] + (float) $row['cod_fee'] + (float) $row['shipping_cost'];
            $grouped[$key]['estimated_profit'] += ((float) $row['selling_price']) - ((float) $row['hpp']);

            $grouped[$key]['details'][] = [
                'product_id' => $row['product_id'],
                'quantity' => (int) $row['quantity'],
                'hpp' => (float) $row['hpp'],
                'unit_selling_price' => (float) $row['selling_price'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        foreach ($grouped as $transaction) {
            $details = $transaction['details'];
            unset($transaction['details']);

            $this->transactionModel->insert($transaction);
            $transactionId = $this->transactionModel->getInsertID();

            foreach ($details as $detail) {
                $detail['transaction_id'] = $transactionId;
                $this->detailModel->insert($detail);

                // Update Stok Inventory & Produk
                $this->updateStock($detail['product_id'], $detail['quantity']);
            }

            // Insert / Update Customer
            $this->upsertCustomer($transaction);
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new DataException('Gagal menyimpan data import.');
        }
    }

    /**
     * 🔁 Update stock setelah transaksi
     */
    private function updateStock(int $productId, int $quantity)
    {
        $product = $this->productModel->find($productId);

        if ($product) {
            $newStock = max(0, $product['stock'] - $quantity);

            $this->productModel->update($product['id'], [
                'stock' => $newStock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Simpan ke stock_transactions
            $this->db->table('stock_transactions')->insert([
                'warehouse_id' => 1, // Soscom gudangnya fix default
                'product_id' => $productId,
                'quantity' => $quantity,
                'transaction_type' => 'Outbound',
                'status' => 'Stock Out',
                'transaction_source' => 'Soscom',
                'reference' => 'SOSCOM_TXN',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 💾 Insert atau update customer
     */
    private function upsertCustomer(array $data)
    {
        $existing = $this->customerModel->where('whatsapp_number', $data['whatsapp_number'])->first();

        if (!$existing) {
            $this->customerModel->insert([
                'whatsapp_number' => $data['whatsapp_number'],
                'customer_name' => $data['customer_name'],
                'city' => $data['city'],
                'province' => $data['province'],
                'total_order' => 1,
                'total_spent' => (float) $data['total_payment'],
                'first_order_date' => $data['date'],
                'last_order_date' => $data['date'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->customerModel->update($existing['id'], [
                'total_order' => $existing['total_order'] + 1,
                'total_spent' => $existing['total_spent'] + (float) $data['total_payment'],
                'last_order_date' => $data['date'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
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
}
