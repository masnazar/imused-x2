<?php namespace App\Controllers;

use App\Services\BrandExpenseService;
use App\Models\AccountModel;
use App\Models\PlatformModel;
use App\Models\BrandModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class BrandExpenses extends Controller
{
    protected BrandExpenseService $service;
    protected AccountModel        $accountModel;
    protected PlatformModel       $platformModel;
    protected BrandModel          $brandModel;

    public function __construct()
    {
        helper(['url','form','session','periode']);
        $this->service       = new BrandExpenseService();
        $this->accountModel  = new AccountModel();
        $this->platformModel = new PlatformModel();
        $this->brandModel    = new BrandModel();
    }

    /** daftar */
    public function index()
    {
        $data = [
            'date_filter' => view('partials/date_filter'),
        ];
        return view('brand_expenses/index', $data);
    }

    /** AJAX DataTables */
    public function getData(): ResponseInterface
    {
        $params = $this->request->getPost();
        $res    = $this->service->getPaginated($params);
        $res[csrf_token()] = csrf_hash();
        return $this->response->setJSON($res);
    }

    /** Tampilkan form tambah */
    public function create()
    {
        return view('brand_expenses/form', [
            'mode'         => 'create',
            'brands'       => $this->brandModel->findAll(),
            'coas'         => $this->accountModel->findAll(),
            'platforms'    => $this->platformModel->findAll(),
            'brandExpense' => [], 
        ]);
    }

    /** Simpan baru */
    public function store()
    {
        $post = $this->request->getPost();
        // pastikan user_id ada di session
        $post['processed_by'] = session()->get('user_id');
        $this->service->create($post);

        return redirect()->to('/brand-expenses')
                         ->with('success','Brand Expense berhasil disimpan.');
    }

    /** Tampilkan form edit */
    public function edit(int $id = null)
    {
        $row = $this->service->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound('Data tidak ditemukan');
        }

        return view('brand_expenses/form', [
            'mode'         => 'edit',
            'brands'       => $this->brandModel->findAll(),
            'coas'         => $this->accountModel->findAll(),
            'platforms'    => $this->platformModel->findAll(),
            'brandExpense' => $row,
        ]);
    }

    /** Update */
    public function update(int $id = null)
    {
        $post = $this->request->getPost();
        $post['processed_by'] = session()->get('user_id');
        $this->service->update($id, $post);

        return redirect()->to('/brand-expenses')
                         ->with('success','Brand Expense berhasil diperbarui.');
    }

    /** Hapus AJAX */
    public function delete(int $id = null): ResponseInterface
    {
        if (! $id) {
            throw PageNotFoundException::forPageNotFound('ID tidak ada');
        }

        $this->service->delete($id);
        return $this->response->setJSON([
            'success'    => true,
            csrf_token() => csrf_hash(),
        ]);
    }
}
