<?php

namespace App\Controllers;

use App\Services\PurchaseOrderService;
use App\Repositories\PurchaseOrderRepository;
use CodeIgniter\Controller;
use App\Models\SupplierModel;
use App\Models\ProductModel;
use App\Services\InventoryService;
use App\Repositories\InventoryRepository;


class PurchaseOrder extends Controller
{
    protected $service;
    protected $supplierModel;
    protected $productModel;
    protected $auditLogService;
    protected $inventoryService;

    public function __construct()
{
    $this->service = new PurchaseOrderService(new PurchaseOrderRepository());
    $this->supplierModel = new SupplierModel();
    $this->productModel = new ProductModel();
    $this->auditLogService = new \App\Services\AuditLogService();
    $this->inventoryService = new InventoryService(new InventoryRepository());
}

    /**
     * 📌 Menampilkan daftar Purchase Orders
     */
    public function index()
{
    log_message('info', '🟢 Memuat daftar Purchase Orders');

    // Ambil data statistik DENGAN FILTER
    $stats = $this->service->getPOStatistics(
        $this->request->getGet() // Pass filter parameters
    );

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
        'growthItems' => $growth['items'],
        'date_filter' => view('partials/date_filter')
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
     * 
     * Fungsi ini menyimpan data Purchase Order baru ke database.
     * Menggunakan transaksi untuk memastikan konsistensi data.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
{
    $validation = \Config\Services::validation();

    $validation->setRules([
        'supplier_id' => 'required|integer',
        'products'    => 'required',
        'products.*.product_id' => 'required|integer',
        'products.*.quantity'   => 'required|integer|greater_than[0]',
        'products.*.unit_price' => 'required|numeric|greater_than[0]',
    ]);

    if (!$this->validate($validation->getRules())) {
        return redirect()->back()->withInput()->with('error', $validation->getErrors());
    }

    $db = \Config\Database::connect();
    $db->transStart();

    try {
        $data = $this->request->getPost();
        log_message('info', '🟢 Menerima data Purchase Order: ' . json_encode($data));

        // Gunakan produk pertama untuk mendapatkan kode brand
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

        // Audit Log setelah transaksi sukses
        $this->auditLogService->log(
            user_id(), // Sesuaikan dengan auth system Anda
            'PurchaseOrder',
            $purchaseOrderId,
            'create',
            null,
            $purchaseOrderData
        );

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
        // Ambil data sebelum dihapus
        $poBeforeDelete = $this->service->getPurchaseOrderById($id);
        
        if (!$poBeforeDelete) {
            return redirect()->back()->with('error', 'PO tidak ditemukan atau sudah dihapus');
        }

        $result = $this->service->deletePurchaseOrder($id);
        
        if ($result) {
            // Tidak perlu toArray() karena sudah dalam bentuk array
            $this->auditLogService->log(
                session()->get('user_id'),
                'PurchaseOrder',
                $id,
                'delete',
                $poBeforeDelete, // Langsung passing array
                null
            );
            
            return redirect()->to('/purchase-orders')->with('success', 'PO berhasil dihapus');
        }
        
        return redirect()->back()->with('error', 'Gagal menghapus PO');
    } catch (\Exception $e) {
        log_message('error', '❌ Error hapus PO: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
    }
}

public function getData()
{
    try {
        $request = \Config\Services::request();
        $poModel = new \App\Models\PurchaseOrderModel();

        $filters = [
            'jenis_filter' => $request->getPost('jenis_filter'),
            'start_date'   => $request->getPost('start_date'),
            'end_date'     => $request->getPost('end_date'),
            'periode'      => $request->getPost('periode'),
        ];

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'] ?? '';

        $data = $poModel->getPurchaseOrderData($start, $length, $searchValue, $filters);
        $totalRecords = $poModel->countPurchaseOrders(null, $filters);
        $filteredRecords = $poModel->countPurchaseOrders($searchValue, $filters);

        $formattedData = array_map(function ($row) {
            return [
                'id'            => $row['id'],
                'po_number'     => $row['po_number'],
                'supplier_name' => $row['supplier_name'] ?? 'N/A',
                'status'        => $row['status'] ?? 'Pending',
                'products'      => $this->formatProducts($row['products'] ?? ''),
                'created_at'    => $row['created_at']
            ];
        }, $data ?? []);

        return $this->response->setJSON([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $formattedData
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
    if (empty($products)) return "-";

    $productsArray = explode('||', $products);
    $productDetails = [];

    foreach ($productsArray as $productString) {
        $parts = explode('::', $productString);
        if (count($parts) >= 4) {
            // Format angka
            $quantity = number_format($parts[2], 0, ',', '.') . ' pcs';
            $price = 'Rp ' . number_format($parts[3], 0, ',', '.');
            
            $productDetails[] = "<div class='d-flex align-items-center mb-2'>
                <div class='flex-grow-1'>
                    <div class='fw-medium'>{$parts[1]}</div>
                    <small class='text-muted'>{$quantity} × {$price}</small>
                </div>
                <span class='badge bg-light text-muted border ms-2'>{$parts[0]}</span>
            </div>";
        }
    }

    return !empty($productDetails) 
        ? implode('', $productDetails) 
        : "-";
}

// Di method view()
public function view($id)
{
    try {
        $po = $this->service->getPurchaseOrderById($id);
        $receiptLogs = $this->service->getReceiptLogs($id);
        $auditLogs = $this->auditLogService->getLogsForModel('PurchaseOrder', $id);

        return view('purchase_orders/view', compact('po', 'receiptLogs', 'auditLogs'));
        
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


// Di PurchaseOrderController.php - method storeReceive
public function storeReceive()
{
    try {
        $data = $this->request->getPost();
        
        // Konversi ke uppercase dan trim
        $data['nomor_surat_jalan'] = strtoupper(trim($data['nomor_surat_jalan']));
        
        // Validasi surat jalan
        if (empty($data['nomor_surat_jalan'])) {
            throw new \Exception("Nomor surat jalan wajib diisi!");
        }

        // Ambil data sebelum update
        $poBefore = $this->service->getPurchaseOrderById($data['purchase_order_id']);

        $this->service->processReceivePo($data);

        // Ambil data setelah update
        $poAfter = $this->service->getPurchaseOrderById($data['purchase_order_id']);

        // Audit Log
        $this->auditLogService->log(
            user_id(),
            'PurchaseOrder',
            $data['purchase_order_id'],
            'receive',
            $poBefore,
            $poAfter
        );

        return redirect()->to('/purchase-orders/view/' . $data['purchase_order_id'])
                         ->with('success', '✅ Penerimaan PO berhasil dicatat!');
        
    } catch (\Exception $e) {
        $errorMessage = str_replace(["\n", "\r"], ' ', $e->getMessage());
        log_message('error', '❌ Error di storeReceive(): ' . $errorMessage);
        return redirect()->back()->withInput()->with('error', $errorMessage);
    }
}

public function get_products_by_supplier($supplier_id)
{
    try {
        $products = $this->service->getProductsBySupplier($supplier_id);
        
        return $this->response->setJSON([
            'status' => 'success',
            'products' => $products
        ]);
        
    } catch (\Exception $e) {
        log_message('error', '❌ Error get_products_by_supplier: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

// Tambahkan endpoint baru di Controller
public function get_product_sku($product_id)
{
    try {
        $product = $this->productModel->select('sku')->find($product_id);
        
        if (!$product) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'sku' => $product['sku']
        ]);

    } catch (\Exception $e) {
        log_message('error', '❌ Error get_product_sku: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}}
