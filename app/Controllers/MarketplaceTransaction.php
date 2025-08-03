<?php

namespace App\Controllers;

use App\Services\MarketplaceTransactionService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;
use App\Helpers\LogTrailHelper;
use App\Models\BrandModel;
use CodeIgniter\API\ResponseTrait;


/**
 * Controller untuk modul Marketplace Transactions
 */
class MarketplaceTransaction extends BaseController
{
    use ResponseTrait;
    protected MarketplaceTransactionService $service;
    protected $brandModel;

    /**
     * Dependency Injection dari Service Layer
     */
    public function __construct()
    {
        $this->service = service('MarketplaceTransactionService');
        $this->brandModel = new BrandModel();
    }

    /**
     * Menampilkan halaman utama transaksi berdasarkan platform
     */
    /**
     * 📌 Menampilkan halaman transaksi marketplace
     */

     /**
 * 🔄 Menampilkan semua transaksi (tanpa filter platform)
 */
public function all()
{
    $brands = $this->brandModel->findAll();

    return view('marketplace_transaction/all', [
        'platform'     => 'all',
        'brands'       => $brands,
        'date_filter'  => view('partials/date_filter')
    ]);
}

/**
 * 📦 DataTables Server-side untuk semua transaksi
 */
public function getDataAll(): ResponseInterface
{
    try {
        $request = service('request');
        $params = [
            'draw'         => $request->getPost('draw'),
            'start'        => $request->getPost('start'),
            'length'       => $request->getPost('length'),
            'search'       => $request->getPost('search')['value'] ?? null,
            'jenis_filter' => $request->getPost('jenis_filter'),
            'periode'      => $request->getPost('periode'),
            'start_date'   => $request->getPost('start_date'),
            'end_date'     => $request->getPost('end_date'),
            'brand_id'     => $request->getPost('brand_id'),
            'platform'     => 'all'
        ];

        $data = $this->service->getPaginatedTransactionsAll($params);
        return $this->response->setJSON($data);
    } catch (\Throwable $e) {
        log_message('error', '[MarketplaceTransaction::getDataAll] ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => 'Gagal memuat data transaksi'
        ])->setStatusCode(500);
    }
}


public function getStatisticsAll()
{
    try {
        $request = \Config\Services::request();

        $filters = [
            'jenis_filter' => $request->getPost('jenis_filter'),
            'periode'      => $request->getPost('periode'),
            'start_date'   => $request->getPost('start_date'),
            'end_date'     => $request->getPost('end_date'),
            'brand_id'     => $request->getPost('brand_id'),
            'platform'     => 'all' // ⬅️ Ini penting
        ];

        $stats = $this->service->getStatisticsAll($filters);

        return $this->response->setJSON(array_merge([
            csrf_token() => csrf_hash()
        ], $stats));
    } catch (\Throwable $e) {
        log_message('error', '[❌ getStatisticsAll] ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => 'Gagal memuat statistik'
        ])->setStatusCode(500);
    }
}

    public function index(string $platform)
{
    $allowedPlatforms = ['shopee', 'tokopedia', 'lazada', 'tiktokshop'];

    if (!in_array(strtolower($platform), $allowedPlatforms)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Platform tidak dikenali.");
    }

    // Ambil data brand untuk dropdown filter
    $brands = $this->brandModel->findAll();

    return view('marketplace_transaction/index', [
        'platform'     => $platform,
        'brands'       => $brands,
        'date_filter'  => view('partials/date_filter'), // asumsi partial lo udah ada jenisFilter, periode, dll
    ]);
}


    /**
     * 📦 Server-side untuk datatables transaksi
     */
    public function getData(string $platform)
    {
        try {
            $request = \Config\Services::request();
    
            $params = [
                'draw'         => $request->getPost('draw'),
                'start'        => $request->getPost('start'),
                'length'       => $request->getPost('length'),
                'search'       => $request->getPost('search')['value'] ?? null,
                'jenis_filter' => $request->getPost('jenis_filter'),
                'periode'      => $request->getPost('periode'),
                'start_date'   => $request->getPost('start_date'),
                'end_date'     => $request->getPost('end_date'),
                'brand_id'     => $request->getPost('brand_id'),
                'platform'     => $platform
            ];
    
            // 🎯 Panggil service dengan param lengkap (termasuk filter)
            $data = $this->service->getPaginatedTransactions($params);
    
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', '❌ Error getData(): ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Gagal memuat data transaksi'
            ])->setStatusCode(500);
        }
    }
    

    /**
     * 📊 Statistik transaksi
     */
    public function getStatistics(string $platform)
{
    try {
        $request = \Config\Services::request();

        $filters = [
            'jenis_filter' => $request->getPost('jenis_filter'),
            'periode'      => $request->getPost('periode'),
            'start_date'   => $request->getPost('start_date'),
            'end_date'     => $request->getPost('end_date'),
            'brand_id'     => $request->getPost('brand_id'),
            'platform'     => $platform
        ];

        $stats = $this->service->getStatistics($filters);

        return $this->response->setJSON(array_merge([
            csrf_token() => csrf_hash()
        ], $stats));
    } catch (\Exception $e) {
        log_message('error', '❌ Error getStatistics(): ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => 'Gagal memuat statistik'
        ])->setStatusCode(500);
    }
}



    /**
     * API DataTables Server-side
     */
    public function getTransactions(string $platform): ResponseInterface
{
    try {
        $request = \Config\Services::request();

         // ✅ Tambahin ini
         $start = null;
         $end = null;

        $params = [
            'draw'         => $request->getPost('draw'),
            'start'        => $request->getPost('start'),
            'length'       => $request->getPost('length'),
            'search'       => $request->getPost('search')['value'] ?? null,
            'jenis_filter' => $request->getPost('jenis_filter'),
            'periode'      => $request->getPost('periode'),
            'start_date'   => $request->getPost('start_date'),
            'end_date'     => $request->getPost('end_date'),
            'brand_id'     => $request->getPost('brand_id'),
            'platform'     => $platform
        ];

        // ✅ Pakai yang ini!
        $data = $this->service->getPaginatedTransactions($params);

        return $this->response->setJSON($data);
    } catch (Throwable $e) {
        log_message('error', '[MarketplaceTransaction::getTransactions] ' . $e->getMessage());
        return $this->response->setJSON(['error' => 'Terjadi kesalahan saat memuat data.'])->setStatusCode(500);
    }
}



public function store(string $platform): ResponseInterface
{
    try {
        // 1. Enhanced Permission Check
        if (!auth()->userHasPermission('create_marketplace_transaction')) {
            LogTrailHelper::log('security', 'Akses ilegal ke store transaction', [
                'platform' => $platform,
                'ip' => $this->request->getIPAddress()
            ]);
            return $this->failForbidden('Akses ditolak untuk operasi ini.');
        }

        // 2. CSRF Protection
        if (!$this->request->is('post')) {
            return $this->failMethodNotAllowed();
        }
        
        // 3. Strict Content Type
        if (!$this->request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')) {
            return $this->failUnsupportedMediaType();
        }

        // 4. Input Processing
        $input = $this->request->getPost();
        $filteredInput = $this->service->sanitizeInput($input, $platform);
        
        // 5. Enhanced Validation
        $validationRules = $this->service->getValidationRules($platform);
        if (!$this->validate($validationRules)) {
            $errors = $this->validator->getErrors();
            LogTrailHelper::log('validation', 'Validasi gagal', $errors);
            return $this->failValidationErrors($errors);
        }

        // 6. Database Transaction
        db_connect()->transStart();
        
        try {
            $id = $this->service->createTransaction($platform, $filteredInput);
            
            // 7. Secure Logging
            LogTrailHelper::log(
                'create',
                'Transaksi marketplace dibuat',
                [
                    'platform' => $platform,
                    'id' => $id,
                    'metadata' => $this->service->getSafeMetadata($input)
                ],
                'low' // Sensitivity level
            );
            
            db_connect()->transCommit();
        } catch (\Throwable $e) {
            db_connect()->transRollback();
            throw $e;
        }

        // 8. Rate Limiting
        $throttler = \Config\Services::throttler();
        if ($throttler->check('transaction-store', 5, MINUTE) === false) {
            return $this->failTooManyRequests('Terlalu banyak permintaan. Silakan coba lagi nanti.');
        }

        // 9. Standardized Response
        return $this->respondCreated([
            'success' => true,
            'data' => [
                'id' => $id,
                'links' => [
                    'self' => site_url("marketplace-transactions/{$platform}/{$id}"),
                    'collection' => site_url("marketplace-transactions/{$platform}")
                ]
            ],
            'message' => lang('Transactions.created')
        ]);

    } catch (\Throwable $e) {
        // 10. Secure Error Handling
        $errorId = uniqid('TRX-ERR-');
        log_message('error', "[{$errorId}] MarketplaceTransaction::store: " . $e->getMessage());
        
        return $this->respond([
            'success' => false,
            'error' => [
                'id' => $errorId,
                'message' => lang('Transactions.creationFailed')
            ]
        ], 500);
    }
}
    /**
     * Tampilkan detail transaksi
     */
    public function detail(string $platform, int $id): string
{
    try {
        $transaction = $this->service->getTransactionDetail($platform, $id);

        if (!$transaction) {
            throw new \Exception("Data transaksi tidak ditemukan.");
        }

        // ✅ Ambil juga produk-produknya
        $productsRaw = $this->service->getTransactionProducts($id);

$totalQty = array_sum(array_column($productsRaw, 'quantity')) ?: 1; // Hindari bagi 0

$products = array_map(function ($item) use ($transaction, $totalQty) {
    $item['discount'] = round($transaction['discount'] * ($item['quantity'] / $totalQty));
    $item['admin_fee'] = round($transaction['admin_fee'] * ($item['quantity'] / $totalQty));
    $item['estimated_profit'] = ($item['unit_selling_price'] * $item['quantity']) 
                               - ($item['hpp'] * $item['quantity']) 
                               - $item['discount'] 
                               - $item['admin_fee'];
    return $item;
}, $productsRaw);

        $tracking = null;
        $trackingSummary = null;

        if (!empty($transaction['last_tracking_data'])) {
            $decoded = json_decode($transaction['last_tracking_data'], true);
            if (is_array($decoded)) {
                $tracking = $decoded;
                $trackingSummary = $decoded['summary'] ?? null;
            }
        }

        return view('marketplace_transaction/detail', [
            'platform'     => $platform,
            'transaction'  => $transaction,
            'products'     => $products,
            'tracking'    => $tracking,
            'trackingSummary' => $trackingSummary
        ]);
    } catch (Throwable $e) {
        log_message('error', '[MarketplaceTransaction::detail] ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menampilkan detail transaksi.');
    }
}



    /**
     * Hapus transaksi secara soft delete
     */
    public function delete(string $platform, int $id): ResponseInterface
    {
        try {
            $this->service->deleteTransaction($id);

            LogTrailHelper::log('delete', 'Menghapus transaksi marketplace', [
                'platform' => $platform,
                'id'       => $id
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.'
            ]);
        } catch (Throwable $e) {
            log_message('error', '[MarketplaceTransaction::delete] ' . $e->getMessage());
            return $this->failServerError('Gagal menghapus transaksi.');
        }
    }
}
