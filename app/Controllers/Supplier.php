<?php

namespace App\Controllers;

use App\Services\SupplierService;
use CodeIgniter\Controller;

/**
 * Controller untuk mengelola data Supplier.
 */
class Supplier extends BaseController
{
    /**
     * @var SupplierService Instance dari SupplierService untuk mengelola logika bisnis Supplier.
     */
    protected $supplierService;

    /**
     * Constructor untuk menginisialisasi SupplierService.
     */
    public function __construct()
    {
        $this->supplierService = new SupplierService();
    }

    /**
     * Menampilkan daftar semua supplier.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $data['suppliers'] = $this->supplierService->getAllSuppliers();
        return view('suppliers/index', $data);
    }

    /**
     * Menampilkan form untuk membuat supplier baru.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        return view('suppliers/create');
    }

    /**
     * Menyimpan data supplier baru ke database.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        $result = $this->supplierService->createSupplier($this->request);

        if (!$result['status']) {
            // Jika validasi gagal, kembali ke halaman sebelumnya dengan pesan error.
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }

        // Jika berhasil, redirect ke halaman daftar supplier dengan pesan sukses.
        return redirect()->to('/suppliers')->with('success', $result['message']);
    }

    /**
     * Menampilkan form untuk mengedit data supplier berdasarkan ID.
     *
     * @param int $id ID dari supplier yang akan diedit.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function edit($id)
    {
        $data['supplier'] = $this->supplierService->getSupplierById($id);
        return view('suppliers/edit', $data);
    }

    /**
     * Memperbarui data supplier berdasarkan ID.
     *
     * @param int $id ID dari supplier yang akan diperbarui.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id)
    {
        $result = $this->supplierService->updateSupplier($id, $this->request);

        if (!$result['status']) {
            // Jika validasi gagal, kembali ke halaman sebelumnya dengan pesan error.
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }

        // Jika berhasil, redirect ke halaman daftar supplier dengan pesan sukses.
        return redirect()->to('/suppliers')->with('success', $result['message']);
    }

    /**
     * Menghapus data supplier berdasarkan ID.
     *
     * @param int $id ID dari supplier yang akan dihapus.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id)
    {
        $result = $this->supplierService->deleteSupplier($id);
        return redirect()->to('/suppliers')->with('success', $result['message']);
    }
}
