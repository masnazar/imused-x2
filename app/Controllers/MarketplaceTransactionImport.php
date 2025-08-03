<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\BrandModel;
use App\Models\ProductModel;
use App\Models\MarketplaceTransactionModel;
use App\Models\MarketplaceDetailTransactionModel;
use App\Models\InventoryModel;

/**
 * Controller khusus untuk operasi impor transaksi marketplace.
 */
class MarketplaceTransactionImport extends BaseController
{
    use ResponseTrait;

    /**
     * Mengimpor file Excel dan melakukan validasi awal.
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

            if (count($rows) > 10000) {
                return $this->respond([
                    'status' => 'error',
                    'errors' => ['Jumlah baris melebihi batas maksimum 4000 baris.'],
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
            log_message('error', '[🚫 ImportExcel Error] ' . $e->getMessage());
            log_message('error', '[Trace] ' . $e->getTraceAsString());

            return $this->respond([
                'status' => 'error',
                'message' => 'Gagal membaca file atau terjadi kesalahan internal.'
            ], 500);
        }
    }

    /**
     * Generate dan download template Excel import transaksi marketplace.
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
     * Menampilkan preview data hasil import sebelum disimpan.
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
     * Menyimpan data hasil impor ke database.
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
                'unit_selling_price' => $row['selling_price'],
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
                    $currentStock = $inventory['stock'];
                    $qty = $detail['quantity'];

                    if ($currentStock < $qty) {
                        $db->transRollback();
                        return redirect()->back()->with('error', "Stok tidak mencukupi untuk produk {$product['product_name']} di gudang {$data['warehouse_id']}. Tersedia: $currentStock, diminta: $qty.");
                    }

                    $inventoryModel->update($inventory['id'], [
                        'stock' => $currentStock - $qty,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // 🔻 Update Product Stock
                $product = $productModel->find($detail['product_id']);
                if ($product) {
                    $newStock = $product['stock'] - $detail['quantity'];
                    $totalStockValue = $newStock * $product['hpp'];

                    $productModel->update($product['id'], [
                        'stock' => $product['stock'] - $detail['quantity'],
                        'hpp' => $product['hpp'],
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
}

