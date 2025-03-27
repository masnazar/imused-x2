<?php

namespace App\Controllers;

use App\Services\MarketplaceTransactionService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller untuk menampilkan dan mengelola transaksi marketplace
 */
class MarketplaceTransaction extends BaseController
{
    protected $service;

    public function __construct(MarketplaceTransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * Menampilkan halaman utama Marketplace Transaction
     */
    public function index()
    {
        return view('marketplace_transaction/index');
    }

    /**
     * Endpoint untuk DataTable Server-Side
     */
    public function getTransactions()
    {
        $request = service('request');
        $filters = [
            'filter_type' => $request->getVar('filter_type'),
            'month' => $request->getVar('month'),
            'year' => $request->getVar('year'),
            'start_date' => $request->getVar('start_date'),
            'end_date' => $request->getVar('end_date'),
            'search' => $request->getVar('search')['value'] ?? null
        ];

        $result = $this->service->getPaginatedData($filters);
        return $this->response->setJSON($result);
    }
}
