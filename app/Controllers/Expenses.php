<?php namespace App\Controllers;

use App\Services\ExpenseService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Expenses extends BaseController
{
    protected ExpenseService $service;

    public function __construct()
    {
        // Pastikan session helper tersedia untuk ambil user_id
        helper('session');
        $this->service = new ExpenseService();
    }

    /**
     * Tampilkan halaman daftar expenses
     */
    public function index()
    {
        // Render partial date_filter dan oper ke view
        $data = [
            'date_filter' => view('partials/date_filter'),
        ];
        return view('expenses/index', $data);
    }

    /**
     * Endpoint AJAX untuk DataTables
     */
    public function getData(): ResponseInterface
    {
        // Ambil parameter DataTables + filter periode dari AJAX
        $params = $this->request->getPost();
        $data   = $this->service->getPaginated($params);

        // Sertakan CSRF token baru agar client bisa update
        $data[csrf_token()] = csrf_hash();

        return $this->response->setJSON($data);
    }

    /**
     * Form tambah expense
     */
    public function create()
    {
        return view('expenses/form', [
            'mode'      => 'create',
            'coas'      => $this->service->getAllCoa(),
            'brands'    => $this->service->getAllBrands(),
            'platforms' => $this->service->getAllPlatforms(),
        ]);
    }

    /**
     * Simpan expense baru
     */
    public function store()
    {
        $input = $this->request->getPost([
            'date',
            'description',
            'account_id',
            'brand_id',
            'platform_id',
            'type',
            'amount',
        ]);
        // Tambahkan user yang memproses
        $input['processed_by'] = session()->get('user_id');

        $this->service->create($input);

        return redirect()
            ->to('/expenses')
            ->with('success', 'Expense tersimpan.');
    }

    /**
     * Form edit expense
     */
    public function edit(int $id)
    {
        $exp = $this->service->find($id);
        if (! $exp) {
            throw PageNotFoundException::forPageNotFound('Expense tidak ditemukan');
        }

        return view('expenses/form', [
            'mode'      => 'edit',
            'expense'   => $exp,
            'coas'      => $this->service->getAllCoa(),
            'brands'    => $this->service->getAllBrands(),
            'platforms' => $this->service->getAllPlatforms(),
        ]);
    }

    /**
     * Update expense
     */
    public function update(int $id)
    {
        $input = $this->request->getPost([
            'date',
            'description',
            'account_id',
            'brand_id',
            'platform_id',
            'type',
            'amount',
        ]);

        $this->service->update($id, $input);

        return redirect()
            ->to('/expenses')
            ->with('success', 'Expense diperbarui.');
    }

    /**
     * Hapus expense (AJAX)
     */
    public function delete(int $id): ResponseInterface
    {
        $this->service->delete($id);

        return $this->response->setJSON([
            'success'    => true,
            csrf_token() => csrf_hash(),
        ]);
    }
}
