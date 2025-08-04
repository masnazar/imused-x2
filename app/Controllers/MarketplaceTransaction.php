<?php

namespace App\Controllers;

use App\Services\MarketplaceTransactionService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;
use App\Helpers\LogTrailHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use CodeIgniter\HTTP\Files\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\MarketplaceTransactionModel;
use App\Models\MarketplaceDetailTransactionModel;
use App\Models\InventoryModel;
use App\Models\ProductModel;
use App\Models\CourierModel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use CodeIgniter\I18n\Time;
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

        $data = $this->service->getPaginatedTransactions($params);
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
        
        // 3. Content-Type Validation
        if (strpos($this->request->getHeaderLine('Content-Type'), 'application/x-www-form-urlencoded') === false) {
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
        $db = db_connect();
        $db->transStart();

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

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
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

    /**
 * Mengimpor file Excel dan validasi awal (group by order_number)
 */
public function importExcel(string $platform): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Akses tidak diizinkan.'
            ], 403);
        }

        $file = $this->request->getFile('file_excel');

        $allowedMimes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            return $this->respond([
                'status' => 'error',
                'errors' => ['Tipe file tidak valid']
            ], 422);
        }

        $allowedExtensions = ['xls', 'xlsx'];
        $maxSize = 5 * 1024 * 1024;
        if (!in_array(strtolower($file->getExtension()), $allowedExtensions)) {
            return $this->respond([
                'status' => 'error',
                'errors' => ['Format file tidak didukung. Gunakan .xls atau .xlsx.']
            ], 422);
        }
        if ($file->getSize() > $maxSize) {
            return $this->respond([
                'status' => 'error',
                'errors' => ['Ukuran file melebihi batas maksimum 5MB.']
            ], 422);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
	// TODO: consider streaming to avoid loading entire sheet into memory

            if (count($rows) > 4000) {
                return $this->respond([
                    'status' => 'error',
                    'errors' => ['Jumlah baris melebihi batas maksimum 4000 baris.']
                ], 422);
            }

            $brandModel = new BrandModel();
            $brands = $brandModel->findAll();
            $brandMapByCode = [];
            foreach ($brands as $brand) {
                $brandMapByCode[$brand['kode_brand']] = $brand;
            }

        $headerMap = [
            'A' => 'date',
            'B' => 'kode_brand',
            'C' => 'order_number',
            'D' => 'tracking_number',
            'E' => 'courier_code',
            'F' => 'store_name',
            'G' => 'warehouse_code',
            'H' => 'sku',
            'I' => 'quantity',
            'J' => 'selling_price',
            'K' => 'discount',
            'L' => 'admin_fee'
        ];

        // Load semua model
        $brandModel       = new BrandModel();
        $productModel     = new ProductModel();
        $warehouseModel   = new \App\Models\WarehouseModel();
        $courierModel     = new \App\Models\CourierModel();
        $inventoryModel   = new InventoryModel();
        $transactionModel = new MarketplaceTransactionModel();

        $errorMessages = [];
        $importedData = [];

        // Buat mapping brand, gudang, kurir
        $brands      = array_column($brandModel->findAll(), null, 'id');
        $warehouses  = array_column($warehouseModel->findAll(), null, 'code');
        $couriers    = array_column($courierModel->findAll(), null, 'courier_code');

        // Ambil semua product sekali aja
        $allProducts = $productModel->findAll();
        $productMap = [];
        foreach ($allProducts as $p) {
            $productMap[$p['brand_id']][$p['sku']] = $p;
        }

        // Ambil transaksi yang sudah ada (untuk cek duplikat)
        $existingTransactions = $transactionModel
            ->select('order_number, tracking_number')
            ->findAll();
        $existingSet = [];
        foreach ($existingTransactions as $tx) {
            $existingSet[$tx['order_number'] . $tx['tracking_number']] = true;
        }

        foreach (array_slice($rows, 1) as $i => $row) {
            $rowNumber = $i + 2;
        
            // 🧼 Lewati baris benar-benar kosong
            if (empty(array_filter($row))) {
                log_message('debug', "ℹ️ Baris $rowNumber dilewati karena kosong.");
                continue;
            }
        
            $rowData = [];
        
            foreach ($headerMap as $col => $key) {
                $rowData[$key] = trim((string)($row[$col] ?? ''));
            }
        
            // 🕓 Validasi tanggal
            if (is_numeric($rowData['date'])) {
                try {
                    $rowData['date'] = ExcelDate::excelToDateTimeObject($rowData['date'])->format('Y-m-d');
                } catch (\Throwable $e) {
                    $errorMessages[] = "Baris $rowNumber: Gagal konversi tanggal dari format numerik.";
                    log_message('error', "❌ Gagal konversi Excel date di baris $rowNumber: " . $e->getMessage());
                    continue;
                }
            } elseif (strtotime($rowData['date'])) {
                $rowData['date'] = date('Y-m-d', strtotime($rowData['date']));
            } else {
                $errorMessages[] = "Baris $rowNumber: Format tanggal tidak valid.";
                continue;
            }

            // Validasi brand
            $brand = $brandMapByCode[$rowData['kode_brand']] ?? null;
            if (!$brand) {
                $errorMessages[] = "Baris $rowNumber: Kode brand '{$rowData['kode_brand']}' tidak ditemukan.";
                continue;
            }
            $rowData['brand_id'] = $brand['id'];

            // Validasi produk (SKU + brand match)
            $product = $productMap[$rowData['brand_id']][$rowData['sku']] ?? null;
            if (!$product) {
                $brandName = esc($brand['brand_name']);
                $errorMessages[] = "Baris $rowNumber: SKU '{$rowData['sku']}' tidak ditemukan pada brand <strong>{$brandName}</strong>.";
                continue;
            }

            // Gudang & kurir
            $warehouse = $warehouses[$rowData['warehouse_code']] ?? null;
            if (!$warehouse) {
                $errorMessages[] = "Baris $rowNumber: Kode gudang '{$rowData['warehouse_code']}' tidak valid.";
                continue;
            }

            $courier = $couriers[$rowData['courier_code']] ?? null;
            if (!$courier) {
                $errorMessages[] = "Baris $rowNumber: Kode kurir '{$rowData['courier_code']}' tidak valid.";
                continue;
            }

            // Validasi numeric
            foreach (['quantity', 'selling_price', 'discount', 'admin_fee'] as $numField) {
                if (!is_numeric($rowData[$numField])) {
                    $errorMessages[] = "Baris $rowNumber: Nilai {$numField} tidak valid.";
                    continue 2;
                }
            }

            // Cek duplikat
            $txKey = $rowData['order_number'] . $rowData['tracking_number'];
            if (isset($existingSet[$txKey])) {
                $errorMessages[] = "Baris $rowNumber: Duplikat transaksi (order/resi sudah ada).";
                continue;
            }

            // Cek stok
            $stock = $inventoryModel->getStock($warehouse['id'], $product['id']);
            if ($stock === null || $stock < (int)$rowData['quantity']) {
                $errorMessages[] = "Baris $rowNumber: Stok tidak mencukupi untuk SKU '{$rowData['sku']}'.";
                continue;
            }

            // ✅ Tambahkan ke hasil akhir
            $importedData[] = [
                'date'           => $rowData['date'],
                'brand_id'       => $brand['id'],
                'order_number'   => $rowData['order_number'],
                'tracking_number'=> $rowData['tracking_number'],
                'courier_id'     => $courier['id'],
                'warehouse_id'   => $warehouse['id'],
                'store_name'     => $rowData['store_name'],
                'sku'            => $rowData['sku'],
                'product_id'     => $product['id'],
                'quantity'       => (int)$rowData['quantity'],
                'selling_price'  => (float)$rowData['selling_price'],
                'discount'       => (float)$rowData['discount'],
                'admin_fee'      => (float)$rowData['admin_fee'],
                'hpp'            => (float)$product['hpp'],
                'platform'       => ucfirst(strtolower($platform))
            ];

            // Limit error max 20 supaya gak overload
            if (count($errorMessages) >= 20) {
                $errorMessages[] = "<strong>Baris lainnya tidak dicek karena error terlalu banyak.</strong>";
                break;
            }
        }
        

        if (!empty($errorMessages)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode('<br>', $errorMessages)
            ]);
        }

        session()->set('importedData', $importedData);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Data berhasil diimpor.',
            'redirect' => base_url("marketplace-transactions/confirm-import/{$platform}"),
            csrf_token() => csrf_hash()
        ]);
    } catch (\Throwable $e) {
            log_message('error', '[\ud83d\udeab ImportExcel Error] ' . $e->getMessage());
            log_message('error', '[Trace] ' . $e->getTraceAsString());

            return $this->respond([
                'status' => 'error',
                'message' => 'Gagal membaca file atau terjadi kesalahan internal.'
            ], 500);
        }
}

    /**
     * Generate dan download template Excel import transaksi marketplace
     *
     * @param string $platform
     * @return ResponseInterface
     */
    public function downloadTemplate(string $platform): ResponseInterface
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        // Header sesuai dengan format final
        $headers = [
            'date', 'kode_brand', 'order_number', 'tracking_number',
            'courier_code', 'store_name', 'warehouse_code', 'sku',
            'quantity', 'selling_price', 'discount', 'admin_fee'
        ];

        // Data contoh
        $sample = [
            date('Y-m-d'), 1, 'INV-20250402-001', 'TRACK123456',
            'JNE', 'Toko ABC', 'GDG1', 'SKU',
            2, 150000, 5000, 3000
        ];

        // Tulis header dan data
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($sample, null, 'A2');

        // Siapkan file Excel untuk download
        $filename = 'data_order_import_' . strtolower($platform) . '_' . date('d-m-Y_H:i:s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        // Output response file
        $response = service('response');
        $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->setHeader('Cache-Control', 'max-age=0');

        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return $response->setBody($excelOutput);
    }

    /**
 * Menampilkan preview data hasil import sebelum disimpan
 */
public function confirmImport(string $platform)
{
    $rawData = session()->get('importedData') ?? [];

    if (empty($rawData)) {
        return redirect()->to(base_url("marketplace-transactions/$platform"))
                         ->with('error', 'Tidak ada data untuk dikonfirmasi.');
    }

    // 🔄 Ambil model
    $brandModel     = model('App\Models\BrandModel');
    $productModel   = model('App\Models\ProductModel');
    $warehouseModel = model('App\Models\WarehouseModel');
    $courierModel   = model('App\Models\CourierModel');

    // 🔍 Ambil semua referensi
    $brands     = array_column($brandModel->findAll(), null, 'id');
    $products   = array_column($productModel->findAll(), null, 'id');
    $warehouses = array_column($warehouseModel->findAll(), null, 'id');
    $couriers   = array_column($courierModel->findAll(), null, 'id');

    // 🧩 Lengkapi data untuk ditampilin
    $displayData = array_map(function ($item) use ($brands, $products, $warehouses, $couriers) {
        $item['brand_name']      = $brands[$item['brand_id']]['brand_name'] ?? '-';
        $item['product_name']    = $products[$item['product_id']]['product_name'] ?? '-';
        $item['sku']             = $products[$item['product_id']]['sku'] ?? '-';
        $item['warehouse_code']  = $warehouses[$item['warehouse_id']]['code'] ?? '-';
        $item['courier_code']    = $couriers[$item['courier_id']]['courier_code'] ?? '-';
        return $item;
    }, $rawData);

    return view('marketplace_transaction/confirm_import', [
        'importedData' => $displayData,
        'platform'     => $platform
    ]);
}

/**
 * Menyimpan data import ke database
 */
public function saveImportedData(string $platform)
{
    $importedData = session()->get('importedData');

    if (empty($importedData)) {
        return redirect()->back()->with('error', 'Tidak ada data untuk disimpan.');
    }

    // ✅ Panggil model dengan benar
    $transactionModel = new MarketplaceTransactionModel();
    $detailModel = new MarketplaceDetailTransactionModel();
    $inventoryModel = new InventoryModel();
    $productModel = new ProductModel();

    // Preload products referenced in imported data
    $productIds = array_unique(array_column($importedData, 'product_id'));
    $products = $productModel->whereIn('id', $productIds)->findAll();
    $productMap = array_column($products, null, 'id');

    $db = db_connect();
    $stockLog = $db->table('stock_transactions');

    $db->transStart();

    $grouped = [];

    foreach ($importedData as $row) {
        $key = $row['order_number'] . '||' . $row['tracking_number'];

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'order_number' => $row['order_number'],
                'tracking_number' => $row['tracking_number'],
                'courier_id' => $row['courier_id'],
                'warehouse_id' => $row['warehouse_id'],
                'store_name' => $row['store_name'],
                'brand_id' => $row['brand_id'],
                'platform' => $row['platform'],
                'date' => $row['date'],
                'discount' => 0,
                'admin_fee' => 0,
                'selling_price' => 0,
                'total_qty' => 0,
                'hpp' => 0,
                'details' => [],
            ];
        }

        $grouped[$key]['total_qty'] += (int)$row['quantity'];
        $grouped[$key]['selling_price'] += (float)$row['selling_price'];
        $grouped[$key]['discount'] += (float)$row['discount'];
        $grouped[$key]['admin_fee'] += (float)$row['admin_fee'];
        $grouped[$key]['hpp'] += ((float)$row['hpp']) * (int)$row['quantity'];

        $grouped[$key]['details'][] = [
            'product_id' => $row['product_id'],
            'quantity' => $row['quantity'],
            'hpp' => $row['hpp'],
            'unit_selling_price' => $row['selling_price'], // 🟢 Tambahin ini
            'total_hpp' => $row['hpp'] * $row['quantity']
        ];
        
    }

    foreach ($grouped as $data) {
        $data['net_revenue'] = $data['selling_price'] - $data['discount'] - $data['admin_fee'];
        $data['gross_profit'] = $data['net_revenue'] - $data['hpp'];
        $data['status'] = 'Processed';
        $data['processed_by'] = session('user_id');
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');

        // ⛳ Simpan ke transaksi utama
        $transactionModel->insert($data);
        $transactionId = $transactionModel->getInsertID();

        foreach ($data['details'] as $detail) {
            $detail['transaction_id'] = $transactionId;
            $detail['created_at'] = $detail['updated_at'] = date('Y-m-d H:i:s');

            $detailModel->insert($detail);

            $product = $productMap[$detail['product_id']] ?? null;

            // 🔻 Update Inventory
            $inventory = $inventoryModel
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $detail['product_id'])
                ->first();

            if ($inventory) {
                $currentStock = $inventory['stock'];
                $qty = $detail['quantity'];

                if ($currentStock < $qty) {
                    $db->transRollback();
                    $productName = $product['product_name'] ?? $detail['product_id'];
                    return redirect()->back()->with('error', "Stok tidak mencukupi untuk produk {$productName} di gudang {$data['warehouse_id']}. Tersedia: $currentStock, diminta: $qty.");
                }

                $inventoryModel->update($inventory['id'], [
                    'stock' => $currentStock - $qty,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 🔻 Update Product Stock
            if ($product) {
                $newStock = $product['stock'] - $detail['quantity'];
                $totalStockValue = $newStock * $product['hpp'];

                $productModel->update($product['id'], [
                    'stock' => $newStock,
                    'hpp' => $product['hpp'], // ⬅️ Tambahkan ini!
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 📝 Simpan ke stock_transactions
            $stockLog->insert([
                'warehouse_id' => $data['warehouse_id'],
                'related_warehouse_id' => $data['warehouse_id'],
                'product_id' => $detail['product_id'],
                'quantity' => $detail['quantity'],
                'transaction_type' => 'Outbound',
                'status' => 'Stock Out',
                'transaction_source' => $data['platform'],
                'reference' => $data['order_number'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
    }

    session()->remove('importedData');
    return redirect()->to(base_url("marketplace-transactions/$platform"))
        ->with('success', 'Data berhasil diimpor dan disimpan.');
}

    /**
     * 🔄 Update status resi secara manual
     */
    public function updateResiStatus(string $platform, int $id): ResponseInterface
    {
        try {
            $request = service('request');

            // 📥 Ambil input dan validasi
            $rules = [
                'status' => 'required|string|in_list[Processed,Dalam Perjalanan,Terkirim,Returned]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    csrf_token() => csrf_hash()
                ])->setStatusCode(422);
            }

            $status = $request->getPost('status');

            // 💾 Update ke database melalui model
            $model = new MarketplaceTransactionModel();
            $model->where(['id' => $id, 'platform' => $platform])
                  ->set([
                      'status' => $status,
                      'last_tracking_status' => strtoupper($status)
                  ])
                  ->update();

            LogTrailHelper::log('update', 'Mengubah status resi transaksi', [
                'platform' => $platform,
                'id'       => $id,
                'status'   => $status
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Status resi berhasil diperbarui.',
                csrf_token() => csrf_hash()
            ]);
        } catch (Throwable $e) {
            log_message('error', '[MarketplaceTransaction::updateResiStatus] ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui status resi.',
                csrf_token() => csrf_hash()
            ])->setStatusCode(500);
        }
    }

public function trackResi()
{
    $request = service('request');
    $courier = $request->getPost('courier');
    $awb     = $request->getPost('awb');

    $apiKey = env('BINDERBYTE_API_KEY'); // pastikan sudah diset di .env

    if (empty($apiKey)) {
        log_message('error', '[MarketplaceTransaction::trackResi] BINDERBYTE_API_KEY is missing');
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'API key Binderbyte tidak ditemukan.'
        ])->setStatusCode(500);
    }

    $url = "https://api.binderbyte.com/v1/track?api_key={$apiKey}&courier={$courier}&awb={$awb}";

    try {
        $client = \Config\Services::curlrequest();
        $response = $client->get($url);

        $result = json_decode($response->getBody(), true);

        if ($result['status'] !== 200) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message'] ?? 'Resi tidak ditemukan.'
            ]);
        }

        $summary = $result['data']['summary'] ?? [];
        $lastStatus = strtolower($summary['status'] ?? '');
        $newStatus = 'Dalam Perjalanan';

        if (str_contains($lastStatus, 'delivered')) {
            $newStatus = 'Terkirim';
        } elseif (str_contains($lastStatus, 'return')) {
            $newStatus = 'Returned';
        }

        log_message('debug', '[📦 TRACKING DEBUG] Data yang akan diupdate: ' . json_encode([
            'status' => $newStatus,
            'last_tracking_data' => $result['data'],
            'last_tracking_status' => strtoupper($summary['status'] ?? '-'),
            'awb' => $awb,
            'courier' => $courier
        ]));

        $model = new \App\Models\MarketplaceTransactionModel();

        $model->where('tracking_number', $awb)
      ->orWhere('tracking_number', strtoupper($awb))
      ->orWhere('tracking_number', strtolower($awb))
      ->set([
          'status'               => $newStatus,
          'last_tracking_data'   => json_encode($result['data']),
          'last_tracking_status' => strtoupper($summary['status'] ?? '-')
      ])
      ->update();



      log_message('debug', '[🛠️ TRACKING UPDATE] Baris yang diupdate: ' . $model->db->affectedRows());

      log_message('debug', '🔗 Request URL: ' . $url);
        

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result['data'],
            csrf_token() => csrf_hash()
        ]);
    } catch (\Throwable $e) {
        log_message('error', '❌ Error trackResi: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menghubungi API.',
            csrf_token() => csrf_hash()
        ]);
    }
}

    /**
     * 🔄 Perbarui status resi berdasarkan ID transaksi melalui API
     */
    public function refreshResiStatus(string $platform, int $id)
    {
        try {
            $transactionModel = new MarketplaceTransactionModel();
            $transaction = $transactionModel
                ->where('platform', $platform)
                ->find($id);

            if (!$transaction) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan.',
                    csrf_token() => csrf_hash()
                ])->setStatusCode(404);
            }

            if (empty($transaction['tracking_number']) || empty($transaction['courier_id'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data resi tidak lengkap.',
                    csrf_token() => csrf_hash()
                ])->setStatusCode(400);
            }

            $courierModel = new CourierModel();
            $courier = $courierModel->find($transaction['courier_id']);

            if (!$courier || empty($courier['courier_code'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Courier tidak ditemukan.',
                    csrf_token() => csrf_hash()
                ])->setStatusCode(400);
            }

            $apiKey = env('BINDERBYTE_API_KEY');
            $courierCode = $courier['courier_code'];
            $awb = $transaction['tracking_number'];
            $url = "https://api.binderbyte.com/v1/track?api_key={$apiKey}&courier={$courierCode}&awb={$awb}";

            $client = \Config\Services::curlrequest();
            $response = $client->get($url);
            $result = json_decode($response->getBody(), true);

            if ($result['status'] !== 200) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Resi tidak ditemukan.',
                    csrf_token() => csrf_hash()
                ]);
            }

            $summary = $result['data']['summary'] ?? [];
            $lastStatus = strtolower($summary['status'] ?? '');
            $newStatus = 'Dalam Perjalanan';

            if (str_contains($lastStatus, 'delivered')) {
                $newStatus = 'Terkirim';
            } elseif (str_contains($lastStatus, 'return')) {
                $newStatus = 'Returned';
            }

            $transactionModel->update($id, [
                'status'               => $newStatus,
                'last_tracking_data'   => json_encode($result['data']),
                'last_tracking_status' => strtoupper($summary['status'] ?? '-')
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $result['data'],
                csrf_token() => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[MarketplaceTransaction::refreshResiStatus] ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupdate status resi.',
                csrf_token() => csrf_hash()
            ])->setStatusCode(500);
        }
    }

}
