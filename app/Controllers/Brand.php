<?php

namespace App\Controllers;

use App\Services\BrandService;
use App\Repositories\BrandRepository;
use CodeIgniter\Controller;
use App\Models\SupplierModel;

/**
 * Controller untuk mengelola data Brand
 */
class Brand extends Controller
{
    protected $brandService;
    protected $supplierModel;

    /**
     * Constructor
     * Inisialisasi service dan model yang dibutuhkan
     */
    public function __construct()
    {
        $this->brandService = new BrandService(new BrandRepository(), \Config\Services::validation());
        $this->supplierModel = new SupplierModel();
    }

    /**
     * 📌 Menampilkan daftar brand
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $data['brands'] = $this->brandService->getAllBrands();
        return view('brands/index', $data);
    }

    /**
     * 📌 Menampilkan form tambah brand
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        $suppliers = $this->supplierModel->findAll();

        return view('brands/create', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * 📌 Proses simpan brand baru
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store()
    {
        $postData = $this->request->getPost();
        $result = $this->brandService->createBrand($postData);

        if (isset($result['error'])) {
            return redirect()->back()->withInput()->with('error', '❌ ' . implode(', ', $result['error']));
        }

        return redirect()->to('/brands')->with('success', '✅ Brand berhasil ditambahkan.');
    }

    /**
     * 📌 Menampilkan form edit brand
     * @param int $id ID brand yang akan diedit
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function edit($id)
    {
        $brand = $this->brandService->getBrandById($id);
        $suppliers = $this->brandService->getAllSuppliers(); // ✅ Ambil supplier untuk dropdown

        if (!$brand) {
            return redirect()->to('/brands')->with('error', '❌ Brand tidak ditemukan.');
        }

        return view('brands/edit', [
            'brand' => $brand,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * 📌 Proses update brand
     * @param int $id ID brand yang akan diperbarui
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        $postData = $this->request->getPost();
        $this->brandService->updateBrand($id, $postData);

        return redirect()->to('/brands')->with('success', '✅ Brand berhasil diperbarui.');
    }

    /**
     * 📌 Hapus brand
     * @param int $id ID brand yang akan dihapus
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        $this->brandService->deleteBrand($id);
        return redirect()->to('/brands')->with('success', '✅ Brand berhasil dihapus.');
    }
}
