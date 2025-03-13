<?php

namespace App\Controllers;

use App\Services\BrandService;
use App\Repositories\BrandRepository;
use CodeIgniter\Controller;
use App\Models\SupplierModel;
use CodeIgniter\Validation\Validation;

class Brand extends Controller
{
    protected $brandService;

    public function __construct()
    {
        $this->brandService = new BrandService(new BrandRepository(), \Config\Services::validation());
        $this->supplierModel = new SupplierModel();
    }

    /**
     * 📌 Menampilkan daftar brand
     */
    public function index()
    {
        $data['brands'] = $this->brandService->getAllBrands();
        return view('brands/index', $data);
    }

    /**
     * 📌 Menampilkan form tambah brand
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
     */
    /**
 * 📌 Menampilkan form edit brand
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
     */
    public function update($id)
    {
        $postData = $this->request->getPost();
        $this->brandService->updateBrand($id, $postData);

        return redirect()->to('/brands')->with('success', '✅ Brand berhasil diperbarui.');
    }

    /**
     * 📌 Hapus brand
     */
    public function delete($id)
    {
        $this->brandService->deleteBrand($id);
        return redirect()->to('/brands')->with('success', '✅ Brand berhasil dihapus.');
    }
}
