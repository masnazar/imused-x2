<?php

namespace App\Services;

use App\Repositories\PurchaseOrderRepository;
use Exception;
use App\Models\StockTransactionModel;
use App\Repositories\ProductRepository;

/**
 * Service untuk mengelola Purchase Order
 */
class PurchaseOrderService
{
    protected $repo;
    protected $inventoryService;
    protected $productRepo;

    /**
     * Konstruktor
     *
     * @param PurchaseOrderRepository $repo
     */
    public function __construct(PurchaseOrderRepository $repo)
    {
        $this->repo = $repo;
        $this->inventoryService = new \App\Services\InventoryService(
            new \App\Repositories\InventoryRepository()
        );
        $this->productRepo = new ProductRepository();
    }

    /**
     * Ambil semua Purchase Orders 
     *
     * @return array
     */
    public function getAllPurchaseOrders()
    {
        return $this->repo->getAllPurchaseOrders();
    }

    /**
     * Ambil statistik Purchase Order
     *
     * @param array $filters
     * @return array
     */
    public function getPOStatistics(array $filters = []): array
{
    $currentStats = $this->repo->getStatisticsWithFilter($filters);

    // ⏮️ Buat filter periode sebelumnya
    $previousFilters = $this->buildPreviousFilters($filters);
    $previousStats = $this->repo->getStatisticsWithFilter($previousFilters);

    return [
        'total'        => $currentStats['total'],
        'pending'      => $currentStats['pending'],
        'completed'    => $currentStats['completed'],
        'total_items'  => $currentStats['total_items'],
        'growth_total'     => $this->calculateGrowth($previousStats['total'], $currentStats['total']),
        'growth_pending'   => $this->calculateGrowth($previousStats['pending'], $currentStats['pending']),
        'growth_completed' => $this->calculateGrowth($previousStats['completed'], $currentStats['completed']),
        'growth_items'     => $this->calculateGrowth($previousStats['total_items'], $currentStats['total_items']),
    ];
}

private function calculateGrowth($old, $current): float
{
    if ($old == 0) {
        return $current > 0 ? 100.0 : 0.0;
    }

    return round((($current - $old) / $old) * 100, 1);
}

private function buildPreviousFilters(array $filters): array
{
    $result = $filters;

    if (($filters['jenis_filter'] ?? '') === 'periode' && !empty($filters['periode'])) {
        [$month, $year] = explode('-', $filters['periode']);
        $prev = (new \DateTime("$year-$month-01"))->modify('-1 month');
        $result['periode'] = $prev->format('m-Y');
    }

    if (($filters['jenis_filter'] ?? '') === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
        $start = new \DateTime($filters['start_date']);
        $end   = new \DateTime($filters['end_date']);

        $diff = $start->diff($end);
        $newEnd = (clone $start)->modify('-1 day');
        $newStart = (clone $newEnd)->sub($diff);

        $result['start_date'] = $newStart->format('Y-m-d');
        $result['end_date'] = $newEnd->format('Y-m-d');
    }

    return $result;
}


    /**
     * Ambil Purchase Order berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function getPurchaseOrderById($id)
    {
        $purchaseOrder = $this->repo->findById($id);

        if (!$purchaseOrder) {
            return null;
        }

        $purchaseOrder['supplier_address'] = $purchaseOrder['supplier_address'] ?? 'Alamat tidak tersedia';
        $purchaseOrder['products'] = $this->repo->getPurchaseOrderDetails($id);

        return $purchaseOrder;
    }

    /**
     * Ambil detail Purchase Order
     *
     * @param int $purchaseOrderId
     * @return array
     */
    public function getPurchaseOrderDetails($purchaseOrderId)
    {
        return $this->repo->getPurchaseOrderDetails($purchaseOrderId);
    }

    /**
     * Ambil log penerimaan terkait dengan Purchase Order
     *
     * @param int $purchaseOrderId
     * @return array
     */
    public function getReceiptLogs($purchaseOrderId)
    {
        return $this->repo->getReceiptLogs($purchaseOrderId);
    }

    /**
     * Ambil semua gudang
     *
     * @return array
     */
    public function getAllWarehouses()
    {
        return $this->repo->getAllWarehouses();
    }

    /**
     * Ambil produk berdasarkan supplier
     *
     * @param int $supplierId
     * @return array
     * @throws Exception
     */
    public function getProductsBySupplier($supplierId)
    {
        $brands = $this->repo->getBrandsBySupplier($supplierId);

        if (empty($brands)) {
            throw new Exception("Supplier tidak memiliki brand");
        }

        $brandIds = array_column($brands, 'id');
        return $this->repo->getProductsByBrands($brandIds);
    }

    /**
     * Generate Nomor PO dengan format: PO/YY/BRN/MM-XXX
     *
     * @param int $productId
     * @return string
     * @throws Exception
     */
    public function generatePoNumber($productId)
    {
        $year = date('y');
        $month = date('n');
        $monthRoman = $this->repo->convertToRoman($month);

        $brandCode = $this->repo->getBrandCodeByProduct($productId);
        if (!$brandCode) {
            throw new Exception("Kode brand tidak ditemukan untuk produk ini.");
        }

        $lastNumber = $this->repo->getLastPoNumber($year, $monthRoman, $brandCode);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "PO/$year/$brandCode/$monthRoman-$newNumber";
    }

    /**
     * Simpan Purchase Order
     *
     * @param array $data
     * @return bool
     */
    public function createPurchaseOrder($data)
    {
        return $this->repo->create($data);
    }

    /**
     * Simpan Detail Purchase Order
     *
     * @param array $data
     * @return bool
     */
    public function createPurchaseOrderDetail($data)
    {
        return $this->repo->createDetail($data);
    }

    /**
     * Hapus Purchase Order dengan Soft Delete
     *
     * @param int $id
     * @return bool
     */
    public function deletePurchaseOrder($id)
    {
        return $this->repo->delete($id);
    }

    /**
     * Proses penerimaan Purchase Order
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function processReceivePo($data)
    {
        log_message('info', '📥 Menerima data penerimaan PO: ' . json_encode($data));

        if (!isset($data['warehouse_id']) || empty($data['warehouse_id'])) {
            throw new Exception("Gudang harus dipilih!");
        }

        if (!isset($data['products']) || !is_array($data['products'])) {
            throw new Exception("Data produk tidak valid!");
        }

        $validProducts = array_filter($data['products'], function ($product) {
            return (int)($product['received_quantity'] ?? 0) > 0;
        });

        if (empty($validProducts)) {
            throw new Exception("Minimal 1 produk dengan jumlah diterima > 0!");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($validProducts as $product) {
                $receivedQty = (int)$product['received_quantity'];

                if ($receivedQty <= 0) {
                    continue;
                }

                // 1️⃣ Update PO Detail
                $this->repo->updateReceivedQuantity(
                    $data['purchase_order_id'],
                    $product['product_id'],
                    $receivedQty
                );

                // 2️⃣ Catat Log Surat Jalan
                $this->repo->logPoReceipt(
                    $data['purchase_order_id'],
                    $product['product_id'],
                    $data['warehouse_id'],
                    $receivedQty,
                    $data['nomor_surat_jalan']
                );

                // 3️⃣ Update Inventory + Log
                $this->inventoryService->increaseStock(
                    $data['warehouse_id'],
                    $product['product_id'],
                    $receivedQty,
                    'Penerimaan PO #' . $data['purchase_order_id'] . ' (Surat Jalan: ' . $data['nomor_surat_jalan'] . ')',
                    'in'
                );

                // 4️⃣ Catat Transaksi Stok (StockTransaction)
                $this->createStockTransaction(
                    $data['warehouse_id'],
                    $product['product_id'],
                    $receivedQty,
                    'Purchase Order' // transaction_source
                );

                $this->productRepo->addStock($product['product_id'], $receivedQty);
            }

            $this->repo->updatePoStatus($data['purchase_order_id']);
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new Exception("Gagal menyimpan penerimaan PO.");
            }

            log_message('info', '✅ Penerimaan PO berhasil diproses.');
            return true;
        } catch (Exception $e) {
            $db->transRollback();
            log_message('error', '❌ Error di processReceivePo(): ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Ambil data Purchase Order sebelum dihapus
     *
     * @param int $id
     * @return array
     */
    public function getPoBeforeDelete($id)
    {
        return $this->repo->findByIdWithDetails($id);
    }

    protected function createStockTransaction($warehouseId, $productId, $qty, $source = 'Purchase Order')
{
    $stockTxModel = new StockTransactionModel();

    $stockTxModel->insert([
        'warehouse_id'          => $warehouseId,
        'product_id'            => $productId,
        'quantity'              => $qty,
        'transaction_type'      => 'Inbound',
        'status'                => 'Received',
        'transaction_source'    => $source,
        'related_warehouse_id'  => $warehouseId, // 🔥 ini dia logikanya
    ]);
}

/**
 * Ambil data untuk DataTables (server-side processing)
 *
 * @param array $params
 * @return array
 */
public function getDataTable(array $params): array
{
    $start = $params['start'] ?? 0;
    $length = $params['length'] ?? 10;
    $search = $params['search']['value'] ?? '';

    $filters = [
        'jenis_filter' => $params['jenis_filter'] ?? 'semua',
        'start_date'   => $params['start_date'] ?? null,
        'end_date'     => $params['end_date'] ?? null,
        'periode'      => $params['periode'] ?? null,
    ];

    // Ambil data utama
    $data = $this->repo->getPurchaseOrderData($start, $length, $search, $filters);

    // Hitung total & filtered records
    $totalRecords    = $this->repo->countAllPurchaseOrders();
    $filteredRecords = $this->repo->countAllPurchaseOrders(
        $this->repo->applyDateFilter(
            $this->repo->buildBaseQuery($search), 'po.created_at', $filters
        )
    );

    log_message('debug', '🎯 FILTER PARAMS: ' . json_encode($filters));


    // Ubah data produk menjadi array mentah
    $rawData = array_map(function ($row) {
        return [
            'id'            => $row['id'],
            'po_number'     => $row['po_number'],
            'supplier_name' => $row['supplier_name'] ?? 'N/A',
            'status'        => $row['status'] ?? 'Pending',
            'products'      => $this->parseProducts($row['products'] ?? ''),
            'created_at'    => $row['created_at']
        ];
    }, $data ?? []);

    return [
        'draw'            => intval($params['draw'] ?? 1),
        'recordsTotal'    => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data'            => $rawData
    ];
}


/**
 * Format string produk menjadi HTML terstruktur
 *
 * @param string $products
 * @return string
 */
private function parseProducts(string $products): array
{
    if (empty($products)) return [];

    $productsArray = explode('||', $products);
    $result = [];

    foreach ($productsArray as $productString) {
        $parts = explode('::', $productString);
        if (count($parts) >= 4) {
            $result[] = [
                'sku'          => $parts[0],
                'nama_produk'  => $parts[1],
                'quantity'     => (int) $parts[2],
                'unit_price'   => (float) $parts[3],
            ];
        }
    }

    return $result;
}
}
