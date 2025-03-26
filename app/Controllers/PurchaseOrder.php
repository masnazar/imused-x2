<?php

namespace App\Controllers;

use App\Services\PurchaseOrderService;
use App\Repositories\PurchaseOrderRepository;
use CodeIgniter\Controller;
use App\Models\SupplierModel;
use App\Models\ProductModel;

class PurchaseOrder extends Controller
{
    protected $service;
    protected $supplierModel;
    protected $productModel;

    public function __construct()
    {
        $this->service = new PurchaseOrderService(new PurchaseOrderRepository());
        $this->supplierModel = new SupplierModel();
        $this->productModel = new ProductModel();
    }

    /**
     * 📌 Menampilkan daftar Purchase Orders
     */
    public function index()
{
    log_message('info', '🟢 Memuat daftar Purchase Orders');

    // Ambil data statistik
    $stats = $this->service->getPOStatistics();

    // Hitung persentase pertumbuhan (contoh dummy)
    $growth = [
        'total' => 12.5,
        'pending' => -3.2,
        'completed' => 8.7,
        'items' => 15.3
    ];

    return view('purchase_orders/index', [
        'totalPOs' => $stats['total'],
        'pendingPOs' => $stats['pending'],
        'completedPOs' => $stats['completed'],
        'totalItems' => $stats['total_items'],
        'growthTotal' => $growth['total'],
        'growthPending' => $growth['pending'],
        'growthCompleted' => $growth['completed'],
        'growthItems' => $growth['items']
    ]);
}

    /**
     * 📌 Menampilkan form tambah Purchase Order
     */
    public function create()
    {
        try {
            $suppliers = $this->supplierModel->findAll();
            $products = $this->productModel->findAll();

            return view('purchase_orders/create', compact('suppliers', 'products'));
        } catch (\Exception $e) {
            log_message('error', '❌ Gagal memuat halaman Create PO: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    /**
     * 📌 Simpan Purchase Order baru
     */
    public function store()
{
    $db = \Config\Database::connect();
    $db->transStart();

    try {
        $data = $this->request->getPost();
        log_message('info', '🟢 Menerima data Purchase Order: ' . json_encode($data));

        if (empty($data['supplier_id'])) {
            throw new \Exception('Supplier tidak boleh kosong.');
        }

        if (empty($data['products'])) {
            throw new \Exception('Produk tidak boleh kosong.');
        }

        // **Gunakan produk pertama untuk mendapatkan kode brand**
        $firstProduct = $data['products'][0]['product_id'];
        $poNumber = $this->service->generatePoNumber($firstProduct);

        // Simpan Purchase Order
        $purchaseOrderData = [
            'po_number'   => $poNumber,
            'supplier_id' => $data['supplier_id'],
            'status'      => 'Pending',
        ];

        $purchaseOrderId = $this->service->createPurchaseOrder($purchaseOrderData);

        if (!$purchaseOrderId) {
            throw new \Exception('Gagal menyimpan Purchase Order.');
        }

        // Simpan Detail Purchase Order
        foreach ($data['products'] as $product) {
            if (!isset($product['product_id'], $product['quantity'], $product['unit_price'])) {
                throw new \Exception('Data produk tidak lengkap.');
            }

            $productData = [
                'purchase_order_id' => $purchaseOrderId,
                'product_id'        => $product['product_id'],
                'quantity'          => $product['quantity'],
                'unit_price'        => $product['unit_price'],
                'total_price'       => $product['quantity'] * $product['unit_price'],
                'received_quantity' => 0,
                'remaining_quantity'=> $product['quantity'],
            ];

            $this->service->createPurchaseOrderDetail($productData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \Exception('Gagal menyimpan Purchase Order.');
        }

        return redirect()->to('/purchase-orders')->with('success', '✅ Purchase Order berhasil dibuat.');
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', '❌ Error saat menyimpan Purchase Order: ' . $e->getMessage());
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
}


    /**
     * 📌 Hapus Purchase Order (Soft Delete)
     */
    public function delete($id)
    {
        try {
            if ($this->service->deletePurchaseOrder($id)) {
                return redirect()->to('/purchase-orders')->with('success', 'Purchase Order berhasil dihapus.');
            }

            return redirect()->back()->with('error', 'Gagal menghapus Purchase Order.');
        } catch (\Exception $e) {
            log_message('error', '❌ Error saat menghapus Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function getData()
{
    try {
        $request = \Config\Services::request();
        $poModel = new \App\Models\PurchaseOrderModel();

        // Ambil parameter DataTables
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'] ?? '';

        // Ambil data dari Model
        $data = $poModel->getPurchaseOrderData($start, $length, $searchValue);
        $totalRecords = $poModel->countAllPurchaseOrders();
        $filteredRecords = $poModel->countFilteredPurchaseOrders($searchValue);

        // Format data untuk DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'id' => $row['id'],
                'po_number' => $row['po_number'],
                'supplier_name' => $row['supplier_name'] ?? 'N/A',
                'status' => $row['status'] ?? 'Pending', // Default status jika null
                'products' => $row['products'] ?? '',
                'created_at' => $row['created_at']
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);

    } catch (\Exception $e) {
        log_message('error', '❌ Error di getData: ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => 'Terjadi kesalahan saat memuat data'
        ])->setStatusCode(500);
    }
}

private function formatProducts($products)
{
    if (empty($products)) return "";

    $productDetails = [];
    $productsArray = explode('||', $products);

    foreach ($productsArray as $productString) {
        $parts = explode('::', $productString);
        if (count($parts) === 5) {
            $productDetails[] = "<b>{$parts[1]}</b> - {$parts[2]} pcs @ Rp " . number_format($parts[3], 0, ',', '.');
        }
    }

    return implode("<br>", $productDetails);
}

public function view($id)
{
    try {
        $po = $this->service->getPurchaseOrderById($id);

        if (!$po) {
            throw new \Exception("Purchase Order tidak ditemukan.");
        }

        log_message('info', '🔍 Data PO di view(): ' . json_encode($po));

        return view('purchase_orders/view', compact('po'));
    } catch (\Exception $e) {
        log_message('error', '❌ Error di view(): ' . $e->getMessage());
        return redirect()->to('/purchase-orders')->with('error', 'Terjadi kesalahan.');
    }
}


public function receive($id)
{
    try {
        $po = $this->service->getPurchaseOrderById($id);
        $warehouses = $this->service->getAllWarehouses(); // Ambil daftar gudang dari service

        if (!$po) {
            throw new \Exception("Purchase Order tidak ditemukan.");
        }

        log_message('info', '🔍 Data PO di receive(): ' . json_encode($po));

        return view('purchase_orders/receive', compact('po', 'warehouses'));
    } catch (\Exception $e) {
        log_message('error', '❌ Error di receive(): ' . $e->getMessage());
        return redirect()->to('/purchase-orders')->with('error', 'Terjadi kesalahan.');
    }
}



public function storeReceive()
{
    try {
        $data = $this->request->getPost();
        $this->service->processReceivePo($data);

        return redirect()->to('/purchase-orders/view/' . $data['purchase_order_id'])
                         ->with('success', 'Penerimaan PO berhasil disimpan.');
    } catch (\Exception $e) {
        log_message('error', '❌ Error di storeReceive(): ' . $e->getMessage());
        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan.');
    }
}

}
