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

/**
 * Controller untuk modul Soscom Transactions
 */
class SoscomTransaction extends BaseController
{
    protected SoscomTransactionService $service;

    public function __construct()
    {
        $this->service = new \App\Services\SoscomTransactionService();
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
            $data = $this->service->getPaginatedTransactions($params);
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
            'B' => 'whatsapp_number',
            'C' => 'customer_name',
            'D' => 'city',
            'E' => 'province',
            'F' => 'brand_code', // ⬅️ GANTI brand_id jadi brand_code
            'G' => 'sku',
            'H' => 'quantity',
            'I' => 'selling_price',
            'J' => 'payment_method',
            'K' => 'cod_fee',
            'L' => 'shipping_cost',
            'M' => 'courier_code',
            'N' => 'tracking_number'
        ];

        $importedData = [];
        $errors = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            $rowData = [];
            $rowNumber = $i + 2;

            foreach ($headerMap as $col => $key) {
                $rowData[$key] = trim((string)($row[$col] ?? ''));
            }

            // 🛠 Format WhatsApp Number
            $rowData['whatsapp_number'] = str_replace(['+', '-', ' '], '', $rowData['whatsapp_number']);
            if (str_starts_with($rowData['whatsapp_number'], '08')) {
                $errors[] = "Baris $rowNumber: Format No. WhatsApp harus diawali 62, bukan 08.";
                continue;
            }
            if (!preg_match('/^62\d{9,12}$/', $rowData['whatsapp_number'])) {
                $errors[] = "Baris $rowNumber: Format No. WhatsApp tidak valid.";
                continue;
            }

            // 🎯 Cari Brand ID dari brand_code
            $brand = model(BrandModel::class)->where('kode_brand', $rowData['brand_code'])->first();
            if (!$brand) {
                $errors[] = "Baris $rowNumber: Kode Brand {$rowData['brand_code']} tidak ditemukan.";
                continue;
            }
            $rowData['brand_id'] = $brand['id'];

            // 🎯 Cari Product ID dari SKU
            $product = model(ProductModel::class)->where('sku', $rowData['sku'])->first();
            if (!$product) {
                $errors[] = "Baris $rowNumber: SKU {$rowData['sku']} tidak ditemukan.";
                continue;
            }
            $rowData['product_id'] = $product['id'];

            // 🛠 Auto Isi HPP dari Produk
            $rowData['hpp'] = $product['hpp'] ?? 0;

            // 🎯 Cari Courier ID dari courier_code
            $courier = model(CourierModel::class)->where('courier_code', $rowData['courier_code'])->first();
            if (!$courier) {
                $errors[] = "Baris $rowNumber: Kode Kurir {$rowData['courier_code']} tidak ditemukan.";
                continue;
            }
            $rowData['courier_id'] = $courier['id'];

            // 📅 Konversi tanggal
            if (is_numeric($rowData['date'])) {
                $rowData['date'] = ExcelDate::excelToDateTimeObject($rowData['date'])->format('Y-m-d');
            }

            $rowData['created_at'] = $rowData['updated_at'] = date('Y-m-d H:i:s');
            $rowData['processed_by'] = user_id();
            $rowData['total_payment'] = (float)$rowData['selling_price'] + (float)$rowData['cod_fee'] + (float)$rowData['shipping_cost'];
            $rowData['estimated_profit'] = (float)$rowData['selling_price'] - (float)$rowData['hpp'];

            $importedData[] = $rowData;
        }

        if (!empty($errors)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode('<br>', $errors)
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
        return $this->failServerError('Gagal memproses file import.');
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

    // Kolom header
    $headers = [
        'Tanggal', 'No WhatsApp', 'Nama Customer', 'Kota', 'Provinsi',
        'Brand ID', 'SKU', 'Qty', 'Harga Jual', 'HPP', 'Payment Method',
        'COD Fee', 'Shipping Cost', 'Courier Code', 'Tracking Number'
    ];

    $sample = [
        date('Y-m-d'), '081234567890', 'Budi', 'Jakarta', 'DKI Jakarta',
        1, 'SKU-001', 2, 50000, 30000, 'COD',
        5000, 10000, 'jne', 'JNE123456'
    ];

    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray($sample, null, 'A2');

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


}
