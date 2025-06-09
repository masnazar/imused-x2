<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CourierModel;

/**
 * Controller untuk manajemen data kurir
 */
class Courier extends BaseController
{
    protected $courierModel;

    public function __construct()
    {
        $this->courierModel = new CourierModel();
    }

    /**
     * Menampilkan halaman daftar kurir
     */
    public function index()
    {
        return view('courier/index');
    }

    /**
     * Menampilkan form tambah kurir
     */
    public function create()
    {
        return view('courier/create');
    }

    /**
     * Menyimpan data kurir baru ke database
     */
    public function store()
    {
        $validation = \Config\Services::validation();

        $data = $this->request->getPost();

        if (!$validation->setRules([
            'courier_name' => 'required|min_length[3]',
            'courier_code' => 'required|alpha_numeric|min_length[2]|max_length[10]',
        ])->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->courierModel->insert([
            'courier_name' => $data['courier_name'],
            'courier_code' => $data['courier_code'],
        ]);

        return redirect()->to('/courier')->with('success', 'Kurir berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kurir
     */
    public function edit($id)
    {
        $courier = $this->courierModel->find($id);

        if (!$courier) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kurir dengan ID $id tidak ditemukan");
        }

        return view('courier/edit', [
            'courier' => $courier
        ]);
    }

    /**
     * Mengupdate data kurir yang sudah ada
     */
    public function update($id)
    {
        $validation = \Config\Services::validation();

        $data = $this->request->getPost();

        if (!$validation->setRules([
            'courier_name' => 'required|min_length[3]',
            'courier_code' => 'required|alpha_numeric|min_length[2]|max_length[10]',
        ])->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->courierModel->update($id, [
            'courier_name' => $data['courier_name'],
            'courier_code' => $data['courier_code'],
        ]);

        return redirect()->to('/courier')->with('success', 'Kurir berhasil diperbarui.');
    }
}
