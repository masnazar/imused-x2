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
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use CodeIgniter\I18n\Time;
use App\Models\BrandModel;
use App\Helpers\ProductFormatter;

/**
 * Controller untuk modul Marketplace Transactions
 */
class MarketplaceTransaction extends BaseController
{
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
        return $this->failServerError('Terjadi kesalahan saat memuat data.');
    }
}



    /**
     * Simpan data transaksi baru
     */
    public function store(string $platform): ResponseInterface
    {
        try {
            $input = $this->request->getPost();

            if (!$this->validate($this->service->getValidationRules())) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $id = $this->service->createTransaction($platform, $input);

            LogTrailHelper::log(
                'create',
                'Menambahkan transaksi marketplace',
                ['platform' => $platform, 'id' => $id]
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan.'
            ]);
        } catch (Throwable $e) {
            log_message('error', '[MarketplaceTransaction::store] ' . $e->getMessage());
            return $this->failServerError('Gagal menyimpan transaksi.');
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
        $products = $this->service->getTransactionProducts($id); // Tambahin ini

        return view('marketplace_transaction/detail', [
            'platform'     => $platform,
            'transaction'  => $transaction,
            'products'     => $products,
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
public function importExcel(string $platform)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Akses tidak diizinkan.'
        ])->setStatusCode(403);
    }

    $file = $this->request->getFile('file_excel');

    if (!$file || !$file->isValid()) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'File tidak valid atau gagal diunggah.'
        ]);
    }

    $ext = $file->getExtension();
    if (!in_array($ext, ['xls', 'xlsx'])) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Format file tidak didukung. Gunakan .xls atau .xlsx.'
        ]);
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // keep Excel format

        $header = [
            'A' => 'date',
            'B' => 'brand_id',
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

        // Load models
        $productModel     = model('App\Models\ProductModel');
        $warehouseModel   = model('App\Models\WarehouseModel');
        $courierModel     = model('App\Models\CourierModel');
        $transactionModel = model('App\Models\MarketplaceTransactionModel');
        $inventoryModel   = model('App\Models\InventoryModel');
        $brandModel       = model('App\Models\BrandModel');

        $importedData = [];
        $errorMessages = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            $rowNumber = $i + 2;

            // Map kolom
            $rowData = [];
            foreach ($header as $col => $key) {
                $rowData[$key] = $row[$col] ?? null;
            }

            // Format tanggal dari Excel
            if (!empty($rowData['date'])) {
                if (is_numeric($rowData['date'])) {
                    // Excel number format
                    $excelDate = ExcelDate::excelToDateTimeObject($rowData['date']);
                    $rowData['date'] = $excelDate->format('Y-m-d');
                } elseif (strtotime($rowData['date'])) {
                    // String fallback (e.g. 02/04/2025)
                    $rowData['date'] = date('Y-m-d', strtotime($rowData['date']));
                } else {
                    $errorMessages[] = "Baris {$rowNumber}: Format tanggal tidak valid.";
                    continue;
                }
            }
            

            // Validasi SKU → Product
            $product = $productModel->where('sku', $rowData['sku'])->first();
            if (!$product) {
                $errorMessages[] = "Baris {$rowNumber}: SKU '{$rowData['sku']}' tidak ditemukan.";
                continue;
            }

            // Validasi Warehouse
            $warehouse = $warehouseModel->where('code', $rowData['warehouse_code'])->first();
            if (!$warehouse) {
                $errorMessages[] = "Baris {$rowNumber}: Kode gudang '{$rowData['warehouse_code']}' tidak ditemukan.";
                continue;
            }

            // Validasi Courier
            $courier = $courierModel->where('courier_code', $rowData['courier_code'])->first();
            if (!$courier) {
                $errorMessages[] = "Baris {$rowNumber}: Kode kurir '{$rowData['courier_code']}' tidak ditemukan.";
                continue;
            }

            // Validasi stok
            $stock = $inventoryModel->getStock($warehouse['id'], $product['id']);
            if ($stock === null || $stock < $rowData['quantity']) {
                $errorMessages[] = "Baris {$rowNumber}: Stok SKU '{$rowData['sku']}' tidak mencukupi.";
                continue;
            }

            // Duplikat order
            $exists = $transactionModel
                ->where('order_number', $rowData['order_number'])
                ->where('tracking_number', $rowData['tracking_number'])
                ->first();
            if ($exists) {
                $errorMessages[] = "Baris {$rowNumber}: Order '{$rowData['order_number']}' / Resi '{$rowData['tracking_number']}' sudah ada.";
                continue;
            }

            // Mapping & enrich data
            $rowData['product_id']    = $product['id'];
            $rowData['hpp']           = $product['hpp'];
            $rowData['warehouse_id']  = $warehouse['id'];
            $rowData['courier_id']    = $courier['id'];
            $rowData['platform']      = ucfirst(strtolower($platform));

            $importedData[] = $rowData;
        }

        if (!empty($errorMessages)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode('<br>', $errorMessages)
            ]);
        }

        session()->set('importedData', $importedData);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Import sukses.',
            'redirect' => base_url("marketplace-transactions/confirm-import/$platform")
        ]);

    } catch (\Throwable $e) {
        log_message('error', '[importExcel] Gagal import: ' . $e->getMessage());
        log_message('error', '[importExcel] Trace: ' . $e->getTraceAsString());

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat membaca file.'
        ]);
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
            'date', 'brand_id', 'order_number', 'tracking_number',
            'courier_code', 'store_name', 'warehouse_code', 'sku',
            'quantity', 'selling_price', 'discount', 'admin_fee'
        ];

        // Data contoh
        $sample = [
            date('Y-m-d'), 1, 'INV-20250402-001', 'TRACK123456',
            'JNE', 'Toko ABC', 'GDG1', 'SKU-001',
            2, 150000, 5000, 3000
        ];

        // Tulis header dan data
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($sample, null, 'A2');

        // Siapkan file Excel untuk download
        $filename = 'template_import_' . strtolower($platform) . '_' . date('Ymd_His') . '.xlsx';
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
    $importedData = session()->get('importedData') ?? [];

    if (empty($importedData)) {
        return redirect()->to(base_url("marketplace-transactions/$platform"))
                         ->with('error', 'Tidak ada data untuk dikonfirmasi.');
    }

    return view('marketplace_transaction/confirm_import', [
        'importedData' => $importedData,
        'platform' => $platform
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

            // 🔻 Update Inventory
            $inventory = $inventoryModel
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $detail['product_id'])
                ->first();

            if ($inventory) {
                $inventoryModel->update($inventory['id'], [
                    'stock' => $inventory['stock'] - $detail['quantity'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 🔻 Update Product Stock
            $product = $productModel->find($detail['product_id']);
            if ($product) {
                $productModel->update($product['id'], [
                    'stock' => $product['stock'] - $detail['quantity'],
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
                'reference' => $transactionId,
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

public function trackResi()
{
    $request = service('request');
    $courier = $request->getPost('courier');
    $awb     = $request->getPost('awb');

    $apiKey = env('BINDERBYTE_API_KEY'); // pastikan sudah diset di .env

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

        $lastStatus = strtolower($result['data']['summary']['status']);
        $newStatus = 'Dalam Perjalanan';

        if (str_contains($lastStatus, 'delivered')) {
            $newStatus = 'Terkirim';
        } elseif (str_contains($lastStatus, 'return')) {
            $newStatus = 'Returned';
        }

        // Update ke DB
        $model = new \App\Models\MarketplaceTransactionModel();
        $model->where('tracking_number', $awb)->set(['status' => $newStatus])->update();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result['data']
        ]);
    } catch (\Throwable $e) {
        log_message('error', '❌ Error trackResi: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menghubungi API.'
        ]);
    }
}

}
