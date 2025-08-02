<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\BrandExpenseService;
use App\Models\AccountModel;
use App\Models\PlatformModel;
use App\Models\BrandModel;
use App\Models\StoreModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class BrandExpenses extends BaseController
{
    protected BrandExpenseService $service;
    protected AccountModel        $accountModel;
    protected PlatformModel       $platformModel;
    protected BrandModel          $brandModel;
    protected StoreModel          $storeModel;

    public function __construct()
    {
        helper(['url','form','session','periode']);
        $this->service       = new BrandExpenseService();
        $this->accountModel  = new AccountModel();
        $this->platformModel = new PlatformModel();
        $this->brandModel    = new BrandModel();
        $this->storeModel    = new StoreModel();
    }

    /** Daftar & kirim date_filter partial */
    public function index()
    {
        return view('brand_expenses/index', [
            'date_filter' => view('partials/date_filter'),
        ]);
    }

    /** AJAX untuk DataTables */
    public function getData(): ResponseInterface
    {
        $params = $this->request->getPost();
        $res    = $this->service->getPaginated($params);
        $res[csrf_token()] = csrf_hash();
        return $this->response->setJSON($res);
    }

    /** Form tambah */
    public function create()
    {
        return view('brand_expenses/form', [
            'mode'         => 'create',
            'brands'       => $this->brandModel->findAll(),
            'stores'       => $this->storeModel->findAll(),
            'coas'         => $this->accountModel->findAll(),
            'platforms'    => $this->platformModel->findAll(),
            'brandExpense' => [],
        ]);
    }

    /** Simpan baru */
    public function store()
    {
        $post = $this->request->getPost();
        $post['processed_by'] = session()->get('user_id');
        $this->service->create($post);

        return redirect()->to('/brand-expenses')
                         ->with('success','Brand Expense berhasil disimpan.');
    }

    /** Form edit */
    public function edit(int $id = null)
    {
        $row = $this->service->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound('Data tidak ditemukan');
        }

        return view('brand_expenses/form', [
            'mode'         => 'edit',
            'brands'       => $this->brandModel->findAll(),
            'stores'       => $this->storeModel->findAll(),
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
