<?php

namespace App\Controllers;

use App\Services\StockTransactionService;

class StockTransaction extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new StockTransactionService();
    }

    public function index()
    {
        $warehouses = $this->service->getAllWarehouses();
        return view('stock_transactions/index', ['warehouses' => $warehouses]);
    }

    public function getStockTransactions()
    {
        try {
            $request = service('request');
            
            // Ambil parameter DataTables
            $draw = $request->getVar('draw');
            $start = $request->getVar('start') ?? 0;
            $length = $request->getVar('length') ?? 10;
            $search = $request->getVar('search')['value'] ?? '';
            $order = $request->getVar('order')[0] ?? [];

            
            // Kolom ordering
            $columns = [
                'warehouse_name',
                'product_name',
                'transaction_type',
                'quantity',
                'status',
                'transaction_source',
                'related_warehouse_name',
                'created_at'
            ];
            $columnIndex = $order['column'] ?? 7;
            $orderColumn = $columns[$columnIndex] ?? 'created_at';
            $orderDir = $order['dir'] ?? 'asc';

            // Filter custom
            $filters = [
                'warehouse_id' => $request->getVar('warehouse_id'),
                'transaction_type' => $request->getVar('transaction_type'),
                'start_date' => $request->getVar('start_date'),
                'end_date' => $request->getVar('end_date')
            ];
            

            $validation = \Config\Services::validation();
            $validation->setRules([
                'warehouse_id' => 'permit_empty|integer',
                'transaction_type' => 'permit_empty|alpha',
                'start_date' => 'permit_empty|valid_date',
                'end_date' => 'permit_empty|valid_date',
            ]);
            if (!$this->validate($validation->getRules())) {
                return $this->response->setJSON([
                    'error' => 'Invalid request parameters',
                    'messages' => $validation->getErrors()
                ]);
            }

            // Panggil Service
            $result = $this->service->getTransactions(
                $search,
                $start,
                $length,
                $orderColumn,
                $orderDir,
                $filters
            );

            // Logging untuk debugging
            log_message('debug', 'Request: ' . print_r($request->getPost(), true));
            log_message('debug', 'SQL: ' . $this->service->getLastQuery());

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $this->service->countAll(),
                'recordsFiltered' => $this->service->countFiltered($search, $filters),
                'data' => $result['data'],
                csrf_token() => csrf_hash() // 🛡️ kirim token baru
            ]);
            
        } catch (\Exception $e) {
            log_message('error', '[Controller Error] ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'An error occurred while retrieving data.'
            ]);
        }
    }

    public function getStatistik()
{
    
    try {
        $request = service('request');

        $filters = [
            'warehouse_id' => $request->getPost('warehouse_id'),
            'transaction_type' => $request->getPost('transaction_type'),
            'start_date' => $request->getPost('start_date'),
            'end_date' => $request->getPost('end_date')
        ];

        // Validasi
        $validation = \Config\Services::validation();
        $validation->setRules([
            'warehouse_id' => 'permit_empty|integer',
            'transaction_type' => 'permit_empty|in_list[Inbound,Outbound,Transfer]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date' => 'permit_empty|valid_date[Y-m-d]',
        ]);

        if (!$validation->run($filters)) {
            return $this->response->setJSON([
                'error' => 'Invalid parameters',
                'messages' => $validation->getErrors()
            ])->setStatusCode(400);
        }

        $data = $this->service->getStatistik($filters);

        return $this->response->setJSON([
            'total_inbound' => (int)$data['total_inbound'],
            'total_outbound' => (int)$data['total_outbound'],
            'chart' => $data['chart'],
            csrf_token() => csrf_hash()
        ]);
    } catch (\Throwable $th) {
        log_message('error', '[Statistik Error] ' . $th->getMessage());
        return $this->response->setJSON([
            'error' => 'Gagal ambil statistik'
        ])->setStatusCode(500);
    }
}

}