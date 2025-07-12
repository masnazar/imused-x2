<?php namespace App\Controllers;

use App\Services\StoreService;
use App\Models\BrandModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Stores extends BaseController
{
    protected StoreService $service;
    protected BrandModel    $brandModel;

    public function __construct()
    {
        helper('session');
        $this->service    = new StoreService();
        $this->brandModel = new BrandModel();
    }

    /** Tampilkan daftar */
    public function index()
    {
        return view('stores/index');
    }

    /** AJAX untuk DataTables */
    public function getData(): ResponseInterface
    {
        $params = $this->request->getPost();
        $data   = $this->service->getPaginated($params);
        $data[csrf_token()] = csrf_hash();
        return $this->response->setJSON($data);
    }

    /** Form tambah */
    public function create()
    {
        return view('stores/form', [
            'mode'   => 'create',
            'brands' => $this->brandModel->findAll(),
            'store'  => [],
        ]);
    }

    /** Simpan baru */
    public function store()
    {
        $post = $this->request->getPost([
            'brand_id','store_code','store_name'
        ]);

        $this->service->create($post);

        return redirect()
            ->to('/stores')
            ->with('success','Toko berhasil disimpan.');
    }

    /** Form edit */
    public function edit($id = null)
    {
        $row = $this->service->find((int)$id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound('Toko tidak ditemukan');
        }

        return view('stores/form', [
            'mode'   => 'edit',
            'brands' => $this->brandModel->findAll(),
            'store'  => $row,
        ]);
    }

    /** Update */
    public function update($id = null)
    {
        $post = $this->request->getPost([
            'brand_id','store_code','store_name'
        ]);

        $this->service->update((int)$id, $post);

        return redirect()
            ->to('/stores')
            ->with('success','Toko berhasil diperbarui.');
    }

    /** Hapus (AJAX) */
    public function delete($id = null): ResponseInterface
    {
        $this->service->delete((int)$id);
        return $this->response->setJSON([
            'success'    => true,
            csrf_token() => csrf_hash(),
        ]);
    }
}
