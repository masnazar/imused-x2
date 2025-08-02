<?php

namespace App\Controllers;

use App\Services\SoscomTransactionService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;
use App\Helpers\LogTrailHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\BrandModel;
use App\Models\ProductModel;
use App\Models\CourierModel;
use App\Models\SoscomTransactionModel;
use App\Models\SoscomTransactionDetailModel;
use App\Models\WarehouseModel;

/**
 * Controller untuk modul Soscom Transactions
 */
class SoscomTransaction extends BaseController
{
    protected SoscomTransactionService $service;
    protected \App\Models\WarehouseModel $warehouseModel;


    public function __construct()
    {
        $this->service = new \App\Services\SoscomTransactionService();
        $this->warehouseModel = new \App\Models\WarehouseModel(); // ⬅️ Tambahin ini
    }

/**
 * 📋 Menampilkan halaman index transaksi
 */
public function index()
{
    return view('soscom_transaction/index', [
        'date_filter' => view('partials/date_filter'),
    ]);
}


    /**
     * 📦 Server-side untuk datatables transaksi
     */
    public function getData()
    {
        try {
            $params = $this->request->getPost();
            $data   = $this->service->getPaginatedTransactions($params);
            log_message('debug', 'Loaded ' . count($data['data']) . ' soscom transactions');
            return $this->response->setJSON($data);
        } catch (Throwable $e) {
            log_message('error', '[SoscomTransaction::getData] ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Gagal memuat data transaksi'])->setStatusCode(500);
        }
    }

    /**
     * 🚀 Store Transaksi Baru
     */
    public function store(): ResponseInterface
    {
        try {
            if (!auth()->userHasPermission('create_soscom_transaction')) {
                return $this->failForbidden('Akses ditolak untuk operasi ini.');
            }

            $input = $this->request->getPost();
            $this->service->storeTransaction($input);

            return $this->respondCreated([
                'message' => 'Transaksi berhasil disimpan.'
            ]);
        } catch (Throwable $e) {
            log_message('error', '[SoscomTransaction::store] ' . $e->getMessage());
            return $this->failServerError('Gagal menyimpan transaksi.');
        }
    }

    /**
     * 📥 Import Excel Soscom
     */
    public function importExcel(): ResponseInterface
{
    try {
        $file = $this->request->getFile('file_excel');

        if (!$file->isValid()) {
            return $this->failValidationErrors(['File tidak valid.']);
        }

        $allowedTypes = ['xls', 'xlsx'];
        if (!in_array($file->getExtension(), $allowedTypes)) {
            return $this->failValidationErrors(['Format file tidak didukung.']);
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) > 4000) {
            return $this->failValidationErrors(['Jumlah baris melebihi batas maksimum 4000.']);
        }

        $headerMap = [
            'A' => 'date',
            'B' => 'phone_number',
            'C' => 'customer_name',
            'D' => 'city',
            'E' => 'province',
            'F' => 'brand_code',
            'G' => 'sku',
            'H' => 'quantity',
            'I' => 'selling_price',
            'J' => 'payment_method',
            'K' => 'shipping_cost',
            'L' => 'cod_fee',
            'M' => 'courier_code',
            'N' => 'tracking_number',
            'O' => 'warehouse_code',
            'P' => 'team_code',
            'Q' => 'channel',
        ];

        


        $importedData = [];
        $errors = [];

        $brandModel = new BrandModel();
        $productModel = new ProductModel();
        $courierModel = new CourierModel();
        $warehouseModel = new \App\Models\WarehouseModel();

        foreach (array_slice($rows, 1) as $i => $row) {
            $rowNumber = $i + 2;
            $rowData = [];

            foreach ($headerMap as $col => $key) {
                $rowData[$key] = trim((string)($row[$col] ?? ''));
            }

            $allowedChannels = ['soscom', 'crm'];
$rowData['channel'] = strtolower(trim($rowData['channel']));
$rowData['channel'] = in_array($rowData['channel'], $allowedChannels) ? $rowData['channel'] : 'soscom';


            // Normalisasi WhatsApp Number
            $rowData['phone_number'] = preg_replace('/[^0-9]/', '', $rowData['phone_number']);
            if (strpos($rowData['phone_number'], '62') !== 0) {
                $errors[] = "Baris $rowNumber: Nomor WhatsApp harus diawali 62.";
                continue;
            }

            // Validasi minimal data wajib
            if (empty($rowData['date']) || empty($rowData['phone_number']) || empty($rowData['sku']) || !is_numeric($rowData['quantity'])) {
                $errors[] = "Baris $rowNumber: Data wajib kosong.";
                continue;
            }

            // Konversi Excel Date
            if (is_numeric($rowData['date'])) {
                $rowData['date'] = ExcelDate::excelToDateTimeObject($rowData['date'])->format('Y-m-d');
            }

            // Cek Brand ID
            $brand = $brandModel->where('kode_brand', $rowData['brand_code'])->first();
            if (!$brand) {
                $errors[] = "Baris $rowNumber: Brand Code '{$rowData['brand_code']}' tidak ditemukan.";
                continue;
            }
            $rowData['brand_id'] = $brand['id'];

            // Cek Product ID + HPP
            $product = $productModel->where('sku', $rowData['sku'])->first();
            if (!$product) {
                $errors[] = "Baris $rowNumber: SKU '{$rowData['sku']}' tidak ditemukan.";
                continue;
            }
            $rowData['product_id'] = $product['id'];
            $rowData['hpp'] = $product['hpp'];

            // Cek Courier ID
            $courier = $courierModel->where('courier_code', $rowData['courier_code'])->first();
            if (!$courier) {
                $errors[] = "Baris $rowNumber: Courier Code '{$rowData['courier_code']}' tidak ditemukan.";
                continue;
            }
            $rowData['courier_id'] = $courier['id'];
            $rowData['channel'] = strtolower(trim($rowData['channel'])) ?: 'soscom';

            try {
                $warehouseCode = $rowData['warehouse_code'];
                $warehouse = $this->warehouseModel->where('code', $warehouseCode)->first();
                if (!$warehouse) {
                    $errors[] = "Baris $rowNumber: Warehouse Code '{$warehouseCode}' tidak ditemukan.";
                    continue;
                }
                $rowData['warehouse_id'] = $warehouse['id'];

                $teamModel = new \App\Models\SoscomTeamModel();
                $team = $teamModel->where('team_code', $rowData['team_code'])->first();
                if (!$team) {
                    $errors[] = "Baris $rowNumber: Team Code '{$rowData['team_code']}' tidak ditemukan.";
                    continue;
                }
                $rowData['soscom_team_id'] = $team['id'];
            } catch (\Throwable $e) {
                log_message('error', $e->getMessage());
                $errors[] = "Baris $rowNumber: Gagal validasi warehouse.";
                continue;
            }

            // Hitung otomatis
            $rowData['total_payment'] = (float)$rowData['selling_price'] + (float)$rowData['shipping_cost'] + (float)$rowData['cod_fee'];
            $rowData['estimated_profit'] = (float)$rowData['selling_price'] - ((float)$rowData['hpp'] * (int)$rowData['quantity']);
            $rowData['created_at'] = $rowData['updated_at'] = date('Y-m-d H:i:s');
            $rowData['processed_by'] = user_id();

            $importedData[] = $rowData;
        }

        // Jika ada error, batalin semua
        if (!empty($errors)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode('<br>', $errors),
            ]);
        }

        session()->set('importedSoscomData', $importedData);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil diimpor.',
            'redirect' => base_url('soscom-transactions/confirm-import'),
            csrf_token() => csrf_hash()
        ]);
    } catch (Throwable $e) {
        log_message('error', '[SoscomTransaction::importExcel] ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Gagal memproses file import.',
            csrf_token() => csrf_hash()
        ]);
    }
}


    /**
     * 📋 Konfirmasi Import
     */
    public function confirmImport()
    {
        $data = session()->get('importedSoscomData') ?? [];

        if (empty($data)) {
            return redirect()->to(base_url('soscom-transactions'))->with('error', 'Tidak ada data untuk dikonfirmasi.');
        }

        return view('soscom_transaction/confirm_import', ['importedData' => $data]);
    }

    /**
     * 🚀 Save Imported Data
     */
    public function saveImportedData()
    {
        try {
            $importedData = session()->get('importedSoscomData');

            if (empty($importedData)) {
                return redirect()->back()->with('error', 'Tidak ada data yang diimport.');
            }

            $this->service->saveImportedData($importedData);

            session()->remove('importedSoscomData');
            return redirect()->to(base_url('soscom-transactions'))->with('success', 'Data berhasil disimpan.');
        } catch (Throwable $e) {
            log_message('error', '[SoscomTransaction::saveImportedData] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data import.');
        }
    }

    /**
     * 📦 Update Status Tracking Resi
     */
    public function trackResi()
    {
        try {
            $input = $this->request->getPost();
            $result = $this->service->trackResi($input['courier'], $input['awb']);

            return $this->response->setJSON($result);
        } catch (Throwable $e) {
            log_message('error', '[SoscomTransaction::trackResi] ' . $e->getMessage());
            return $this->failServerError('Gagal tracking resi.');
        }
    }

    /**
 * 📥 Download Template Excel untuk import transaksi Soscom
 */
public function downloadTemplate(): ResponseInterface
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Template Import Soscom');

    // Header kolom
    $headers = [
        'Tanggal (yyyy-mm-dd)',
        'No WhatsApp (62xxxx)',
        'Nama Customer',
        'Kota',
        'Provinsi',
        'Kode Brand',
        'SKU Produk',
        'Quantity',
        'Harga Jual per Produk',
        'Metode Bayar',
        'Ongkir',
        'COD Fee',
        'Kode Courier',
        'No Resi',
        'Kode Warehouse',
        'Kode Team',
        'Channel Sales (soscom/crm)',
    ];

    // Contoh data sample
    $sampleData = [
        date('Y-m-d'),
        '6281234567890',
        'Budi',
        'Jakarta',
        'DKI Jakarta',
        'PHR',
        'PK',
        2,
        150000,
        'COD',
        10000,
        5000,
        'JNT',
        'JNT123456789',
        'KODE GUDANG',
        'TEAM A',
        'crm',
    ]; 

    // Isi header
    $sheet->fromArray([$headers], null, 'A1');

    // Isi sample
    $sheet->fromArray([$sampleData], null, 'A2');

    // Auto-size semua kolom
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Siapkan file download
    $filename = 'template_import_soscom_' . date('d-m-Y_His') . '.xlsx';
    $writer = new Xlsx($spreadsheet);

    $response = service('response');
    $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"');
    $response->setHeader('Cache-Control', 'max-age=0');

    ob_start();
    $writer->save('php://output');
    $excelOutput = ob_get_clean();

    return $response->setBody($excelOutput);
}

public function getStatistics()
{
    try {
        $data = $this->service->getSummaryStatistics(); // ⬅️ Bikin di service
        return $this->response->setJSON(array_merge($data, [csrf_token() => csrf_hash()]));
    } catch (\Throwable $e) {
        log_message('error', '[SoscomTransaction::getStatistics] ' . $e->getMessage());
        return $this->failServerError('Gagal memuat statistik.');
    }
}


}
