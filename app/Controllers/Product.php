<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use App\Models\BrandModel;
use Exception;

class Product extends BaseController
{
    protected $productService;
    protected $brandModel;

    public function __construct()
    {
        $this->productService = new ProductService(
            new ProductRepository(), 
            \Config\Services::validation(),
            \Config\Services::logger() // ✅ Tambahkan Logger sebagai parameter ketiga
        );
        
        $this->brandModel = new BrandModel();
    }

    /**
     * 📌 Menampilkan daftar produk
     */
    public function index()
    {
        log_message('info', '🟢 Memuat daftar produk');

        try {
            $products = $this->productService->getAllProducts();
            log_message('info', '🔍 Data Produk: ' . json_encode($products));
        } catch (Exception $e) {
            log_message('error', '❌ Gagal memuat daftar produk: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat daftar produk.');
        }

        return view('products/index', ['products' => $products]);
    }

    /**
     * 📌 Menampilkan form tambah produk
     */
    public function create()
    {
        try {
            $brands = $this->brandModel->findAll();
        } catch (Exception $e) {
            log_message('error', '❌ Gagal mengambil data brand: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data brand.');
        }

        return view('products/create', ['brands' => $brands]);
    }

    /**
     * 📌 Proses simpan produk baru
     */
    public function store()
    {
        log_message('info', '🟢 Memulai proses penyimpanan produk');

        try {
            $postData = $this->request->getPost();
            log_message('info', '🔍 Data yang diterima: ' . json_encode($postData));

            $result = $this->productService->createProduct($postData);

            if (isset($result['error'])) {
                throw new Exception(is_array($result['error']) ? implode(', ', $result['error']) : $result['error']);
            }

            log_message('info', '✅ Produk berhasil ditambahkan.');
            return redirect()->to('/products')->with('success', '✅ Produk berhasil ditambahkan.');
        } catch (Exception $e) {
            log_message('error', '❌ Gagal menyimpan produk: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * 📌 Menampilkan form edit produk
     */
    public function edit($id)
    {
        try {
            $data['product'] = $this->productService->getProductById($id);
            $data['brands'] = $this->brandModel->findAll();

            if (!$data['product']) {
                throw new Exception('Produk tidak ditemukan.');
            }

            return view('products/edit', $data);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal memuat data produk untuk edit: ' . $e->getMessage());
            return redirect()->to('/products')->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * 📌 Proses update produk
     */
    public function update($id)
    {
        log_message('info', '🟢 Memulai proses update produk');

        try {
            $postData = $this->request->getPost();
            log_message('info', '🔍 Data yang diterima: ' . json_encode($postData));

            $this->productService->updateProduct($id, $postData);
            log_message('info', '✅ Produk berhasil diperbarui.');

            return redirect()->to('/products')->with('success', '✅ Produk berhasil diperbarui.');
        } catch (Exception $e) {
            log_message('error', '❌ Gagal memperbarui produk: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * 📌 Hapus produk
     */
    public function delete($id)
    {
        log_message('info', '🟢 Memulai proses penghapusan produk ID: ' . $id);

        try {
            $this->productService->deleteProduct($id);
            log_message('info', '✅ Produk berhasil dihapus.');

            return redirect()->to('/products')->with('success', '✅ Produk berhasil dihapus.');
        } catch (Exception $e) {
            log_message('error', '❌ Gagal menghapus produk: ' . $e->getMessage());
            return redirect()->to('/products')->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
 * 🔮 Forecast stok produk berdasarkan historical sales
 *
 * @return \CodeIgniter\HTTP\ResponseInterface
 */
public function forecastStock()
{
    $this->validate([
        'product_id'     => 'required|integer',
        'start_date'     => 'required|valid_date[Y-m-d]',
        'end_date'       => 'required|valid_date[Y-m-d]',
        'forecast_days'  => 'required|integer|min_length[1]',
    ]);

    try {
        /** @var \App\Services\ForecastService $forecastService */
        $forecastService = service(ForecastService::class);

        $productId     = (int) $this->request->getPost('product_id');
        $startDate     = $this->request->getPost('start_date');
        $endDate       = $this->request->getPost('end_date');
        $forecastDays  = (int) $this->request->getPost('forecast_days');

        $result = $forecastService->forecastProductStock($productId, $startDate, $endDate, $forecastDays);

        return $this->response->setJSON([
            csrf_token() => csrf_hash(),
            'status'     => isset($result['error']) ? 'error' : 'success',
            'data'       => $result
        ]);
    } catch (\Throwable $e) {
        log_message('error', '[❌ forecastStock] ' . $e->getMessage());
        return $this->response->setJSON([
            csrf_token() => csrf_hash(),
            'status'     => 'error',
            'message'    => 'Terjadi kesalahan saat proses forecast.'
        ]);
    }
}
}
