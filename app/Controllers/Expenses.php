<?php namespace App\Controllers;

use App\Services\ExpenseService;
use App\Services\AiService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Expenses extends BaseController
{
    protected ExpenseService $service;
    protected AiService      $ai;

    public function __construct()
    {
        // Pastikan session helper tersedia untuk ambil user_id
        helper('session');
        $this->service = new ExpenseService();
        $this->ai  = new AiService();
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

   public function getStatistics(): \CodeIgniter\HTTP\ResponseInterface
    {
        $params = $this->request->getPost();
        $stats  = $this->service->getStatistics($params);

        // kirim token baru
        $stats[csrf_token()] = csrf_hash();
        return $this->response->setJSON($stats);
    }

    /**
     * AJAX: Analisis & Insight
     */
    public function analyze(): ResponseInterface
    {
        $params = $this->request->getPost();

        // ambil statistik dasar
        $stats = $this->service->getStatistics($params);

        // ambil breakdown
        $stats['breakdown_coa']      = $this->service->getCostByCoa($params);
        $stats['breakdown_platform'] = $this->service->getCostByPlatform($params);

        // Minta AI
        $insight = $this->ai->analyze('expenses', $stats);

        // sertakan token & insight dalam response
        $stats[csrf_token()] = csrf_hash();
        $stats['insight']    = $insight;

        return $this->response->setJSON($stats);
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
