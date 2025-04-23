<?php

namespace App\Controllers;

use App\Services\SoscomTransactionService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\ProductModel;
use App\Models\BrandModel;
use App\Models\WarehouseModel;
use App\Models\InventoryModel;
use App\Models\CustomerModel;
use App\Models\SoscomTransactionModel;
use App\Models\SoscomDetailTransactionModel;

/**
 * Controller untuk modul Transaksi Soscom
 */
class SoscomTransaction extends BaseController
{
    protected SoscomTransactionService $service;

    public function __construct()
    {
        $this->service = new SoscomTransactionService(); // ✅ FIXED
    }

    public function index()
    {
        $brands = model(BrandModel::class)->findAll();

        return view('soscom_transaction/index', [
            'brands' => $brands,
            'date_filter' => view('partials/date_filter'),
        ]);
    }

    public function downloadTemplate(): ResponseInterface
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        $headers = [
            'date', 'order_number', 'customer_name', 'phone_number', 'city', 'province',
            'brand_id', 'store_name', 'warehouse_code', 'sku', 'quantity',
            'selling_price', 'discount', 'admin_fee', 'payment_method', 'cod_fee', 'lead_source', 'order_type'
        ];

        $sample = [
            date('Y-m-d'), 'INV-20250402-001', 'Budi', '6281234567890', 'Banyumas', 'Jawa Tengah',
            1, 'Toko Sosmed', 'GDG1', 'SKU001', 2,
            150000, 10000, 3000, 'COD', 5000, 'Tim A', 'First Order'
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($sample, null, 'A2');

        $filename = 'template_import_soscom_' . date('Ymd_His') . '.xlsx';
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

    public function importExcel(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Akses tidak diizinkan.');
        }

        $file = $this->request->getFile('file_excel');
        $allowedMime = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        if (!$file->isValid() || !in_array($file->getMimeType(), $allowedMime)) {
            return $this->failValidationErrors(['File tidak valid atau corrupt.']);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            [$data, $errors] = $this->service->processExcelRows($rows);

            if (!empty($errors)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => implode('<br>', $errors)
                ]);
            }

            session()->set('soscom_import', $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data valid dan siap disimpan.',
                'redirect' => base_url('soscom-transactions/confirm-import'),
                csrf_token() => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', '❌ Error Soscom Import: ' . $e->getMessage());
            return $this->failServerError('Gagal membaca file.');
        }
    }

    public function confirmImport()
    {
        $imported = session()->get('soscom_import');

        if (empty($imported)) {
            return redirect()->to(base_url('soscom-transactions'))->with('error', 'Tidak ada data yang ditemukan.');
        }

        return view('soscom_transaction/confirm_import', [
            'importedData' => $imported
        ]);
    }

    public function saveImportedData()
    {
        $data = session()->get('soscom_import');

        if (empty($data)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dapat disimpan.');
        }

        try {
            $this->service->saveImported($data);
            session()->remove('soscom_import');
            return redirect()->to(base_url('soscom-transactions'))->with('success', 'Data berhasil disimpan!');
        } catch (Throwable $e) {
            log_message('error', '❌ Gagal simpan import: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
