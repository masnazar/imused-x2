<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\CustomerService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class CustomerServices extends BaseController
{
    protected CustomerService $service;

    public function __construct()
    {
        helper(['url','form','session']);
        $this->service = new CustomerService();
    }

    /** Daftar */
    public function index()
    {
        return view('customer_services/index');
    }

    /** AJAX untuk DataTables */
    public function getData(): ResponseInterface
    {
        $params = $this->request->getPost();
        $res    = $this->service->getPaginated($params);
        // sertakan CSRF
        $res[csrf_token()] = csrf_hash();
        return $this->response->setJSON($res);
    }

    /** Form tambah */
    public function create()
    {
        return view('customer_services/form', [
            'mode' => 'create',
            'cs'   => [], 
        ]);
    }

    /** Simpan */
    public function store()
    {
        $post = $this->request->getPost(['kode_cs','nama_cs']);
        try {
            $this->service->create($post);
            return redirect()->to('/customer-services')
                             ->with('success','Customer Service berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error',$e->getMessage());
        }
    }

    /** Form edit */
    public function edit(int $id)
    {
        $row = $this->service->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound('Data tidak ditemukan');
        }
        return view('customer_services/form', [
            'mode' => 'edit',
            'cs'   => $row,
        ]);
    }

    /** Update */
    public function update(int $id)
    {
        $post = $this->request->getPost(['kode_cs','nama_cs']);
        try {
            $this->service->update($id, $post);
            return redirect()->to('/customer-services')
                             ->with('success','Customer Service berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error',$e->getMessage());
        }
    }

    /** Hapus (AJAX) */
    public function delete(int $id): ResponseInterface
    {
        $ok = $this->service->delete($id);
        return $this->response->setJSON([
            'success'     => $ok,
            csrf_token() => csrf_hash(),
        ]);
    }
}
