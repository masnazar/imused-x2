<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PurchaseOrderService;
use App\Repositories\PurchaseOrderRepository;
use App\Models\SupplierModel;
use App\Models\ProductModel;
use App\Services\InventoryService;
use App\Repositories\InventoryRepository;
use App\Helpers\ProductFormatter;


class PurchaseOrder extends BaseController
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

    return view('purchase_orders/index', [
        'date_filter' => view('partials/date_filter') // only filter
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

        // buat PO number dan simpan header PO
        $firstProduct = $data['products'][0]['product_id'];
        $poNumber = $this->service->generatePoNumber($firstProduct);

        $purchaseOrderData = [
            'po_number'   => $poNumber,
            'supplier_id' => $data['supplier_id'],
            'status'      => 'Pending',
        ];
        $purchaseOrderId = $this->service->createPurchaseOrder($purchaseOrderData);

        // simpan detail PO, **tanpa** 'total_price'
        foreach ($data['products'] as $item) {
            $productData = [
                'purchase_order_id' => $purchaseOrderId,
                'product_id'        => $item['product_id'],
                'quantity'          => $item['quantity'],
                'unit_price'        => $item['unit_price'],
                // 'total_price'    => $item['quantity'] * $item['unit_price'], // HAPUS!
                'received_quantity' => 0,
                'remaining_quantity'=> $item['quantity'],
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
        $params = $this->request->getPost(); // ✅ Harus POST

        $data = $this->service->getDataTable($params); // ⬅️ Pastikan ini DIKIRIM ke service

        foreach ($data['data'] as &$row) {
            $row['products'] = ProductFormatter::format($row['products']);
        }

        return $this->response->setJSON(array_merge([
            csrf_token() => csrf_hash()
        ], $data));
    } catch (\Throwable $e) {
        log_message('error', '[PurchaseOrder::getData] ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => 'Gagal memuat data PO'
        ])->setStatusCode(500);
    }
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
}
/**
 * 🔄 Ambil statistik berdasarkan filter (dipakai oleh AJAX)
 */
public function getStatistics()
{
    try {
        $filters = $this->request->getPost();
        $stats = $this->service->getPOStatistics($filters);

        return $this->response->setJSON(array_merge([
            csrf_token() => csrf_hash()
        ], $stats));
    } catch (\Throwable $e) {
        log_message('error', '[PurchaseOrder::getStatistics] ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => 'Gagal memuat statistik PO'
        ])->setStatusCode(500);
    }
}

}
