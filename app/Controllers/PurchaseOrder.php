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

        $purchaseOrders = $this->service->getAllPurchaseOrders();

        log_message('info', '🔍 Data Purchase Orders: ' . json_encode($purchaseOrders));

        return view('purchase_orders/index', compact('purchaseOrders'));
    }

    /**
     * 📌 Menampilkan form tambah Purchase Order
     */
    public function create()
    {
        try {
            $suppliers = $this->supplierModel->findAll(); // 🔥 Ambil semua supplier
            $products = $this->productModel->findAll(); // 🔥 Ambil semua produk

            return view('purchase_orders/create', compact('suppliers', 'products'));
        } catch (\Exception $e) {
            log_message('error', '❌ Gagal memuat halaman Create PO: ' . $e->getMessage());
            return redirect()->back()->with('swal_error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    /**
     * 📌 Simpan Purchase Order baru
     */
    public function store()
    {
        try {
            $data = $this->request->getPost();
            log_message('info', '🟢 Menerima data Purchase Order: ' . json_encode($data));

            if ($this->service->createPurchaseOrder($data)) {
                return redirect()->to('/purchase-orders')->with('swal_success', 'Purchase Order berhasil dibuat.');
            }

            return redirect()->back()->with('swal_error', 'Gagal membuat Purchase Order.');
        } catch (\Exception $e) {
            log_message('error', '❌ Error saat menyimpan Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('swal_error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * 📌 Hapus Purchase Order (Soft Delete)
     */
    public function delete($id)
    {
        try {
            if ($this->service->deletePurchaseOrder($id)) {
                return redirect()->to('/purchase-orders')->with('swal_success', 'Purchase Order berhasil dihapus.');
            }

            return redirect()->back()->with('swal_error', 'Gagal menghapus Purchase Order.');
        } catch (\Exception $e) {
            log_message('error', '❌ Error saat menghapus Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('swal_error', 'Terjadi kesalahan sistem.');
        }
    }
}
