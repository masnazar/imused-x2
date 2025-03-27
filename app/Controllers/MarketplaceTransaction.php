<?php

namespace App\Controllers;

use App\Services\MarketplaceTransactionService;
use CodeIgniter\HTTP\ResponseInterface;

class MarketplaceTransaction extends BaseController
{
    protected MarketplaceTransactionService $service;

    public function __construct()
    {
        $this->service = service('MarketplaceTransactionService');
    }

    /**
     * Menampilkan halaman utama transaksi berdasarkan platform
     */
    public function index(string $platform)
    {
        $brandId = $this->request->getGet('brand');
        return view('marketplace_transaction/index', compact('platform', 'brandId'));
    }

    /**
     * Ambil data transaksi untuk DataTables
     */
    public function getTransactions(string $platform)
    {
        return $this->response->setJSON(
            $this->service->getDataTable($this->request, $platform)
        );
    }

    /**
     * Statistik dashboard
     */
    public function getStatistics(string $platform)
    {
        return $this->response->setJSON(
            $this->service->getStatistics($this->request, $platform)
        );
    }

    public function create(string $platform)
    {
        // Akan diisi kemudian
    }

    public function store(string $platform)
    {
        // Akan diisi kemudian
    }

    public function edit(string $platform, int $id)
    {
        // Akan diisi kemudian
    }

    public function update(string $platform, int $id)
    {
        // Akan diisi kemudian
    }

    public function delete(string $platform, int $id)
    {
        // Akan diisi kemudian
    }

    public function detail(string $platform, int $id)
    {
        // Akan diisi kemudian
    }
}
