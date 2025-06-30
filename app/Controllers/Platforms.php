<?php namespace App\Controllers;

use App\Services\PlatformService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Platforms extends BaseController
{
    protected PlatformService $service;

    public function __construct()
    {
        $this->service = new PlatformService();
    }

    /** Tampilkan halaman daftar Platforms */
    public function index()
    {
        return view('platforms/index');
    }

    /** Endpoint AJAX untuk DataTables */
    public function getData(): ResponseInterface
    {
        $params = $this->request->getPost();
        $data   = $this->service->getPaginated($params);
        // Sertakan kembali CSRF token
        $data[csrf_token()] = csrf_hash();
        return $this->response->setJSON($data);
    }

    /** Form tambah */
    public function create()
    {
        return view('platforms/form', [
            'mode' => 'create',
        ]);
    }

    /** Simpan baru */
    public function store()
    {
        $input = $this->request->getPost(['code','name']);
        $this->service->create($input);
        return redirect()->to('/platforms')->with('success','Platform tersimpan.');
    }

    /** Form edit */
    public function edit($id)
    {
        $pl = $this->service->find((int)$id);
        if (! $pl) {
            throw PageNotFoundException::forPageNotFound('Platform tidak ditemukan');
        }
        return view('platforms/form', [
            'mode'     => 'edit',
            'platform' => $pl,
        ]);
    }

    /** Update */
    public function update($id)
    {
        $input = $this->request->getPost(['code','name']);
        $this->service->update((int)$id, $input);
        return redirect()->to('/platforms')->with('success','Platform diperbarui.');
    }

    /** Hapus (AJAX) */
    public function delete($id): ResponseInterface
    {
        $this->service->delete((int)$id);
        return $this->response->setJSON([
            'success'      => true,
            csrf_token()   => csrf_hash(),
        ]);
    }
}
