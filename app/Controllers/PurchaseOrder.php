<?php

namespace App\Controllers;

use App\Services\PurchaseOrderService;
use App\Repositories\PurchaseOrderRepository;
use CodeIgniter\Controller;

class PurchaseOrder extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new PurchaseOrderService(new PurchaseOrderRepository());
    }

    public function index()
    {
        $purchaseOrders = $this->service->getAllPurchaseOrders();
        return view('purchase_orders/index', compact('purchaseOrders'));
    }

    public function create()
    {
        return view('purchase_orders/create');
    }

    public function store()
    {
        $data = $this->request->getPost();

        if ($this->service->createPurchaseOrder($data)) {
            return redirect()->to('/purchase-orders')->with('swal_success', 'PO berhasil dibuat.');
        }

        return redirect()->back()->with('swal_error', 'Gagal membuat PO.');
    }

    public function delete($id)
    {
        if ($this->service->deletePurchaseOrder($id)) {
            return redirect()->to('/purchase-orders')->with('swal_success', 'PO berhasil dihapus.');
        }

        return redirect()->back()->with('swal_error', 'Gagal menghapus PO.');
    }
}
