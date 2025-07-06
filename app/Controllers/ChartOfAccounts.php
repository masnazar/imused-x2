<?php namespace App\Controllers;

use App\Services\ChartOfAccountsService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class ChartOfAccounts extends BaseController
{
    protected ChartOfAccountsService $service;

    public function __construct()
    {
        // Langsung instansiasi service
        $this->service = new ChartOfAccountsService();
    }

    /**
     * Tampilkan daftar COA
     */
    public function index()
    {
        return view('chart_of_accounts/index');
    }

    /**
     * Endpoint AJAX untuk DataTables
     */
    public function getData(): ResponseInterface
    {
        $params = $this->request->getPost();
        $data   = $this->service->getPaginated($params);
        return $this->response->setJSON($data);
    }

    /**
     * Form tambah akun
     */
    public function create()
    {
        $parents = $this->service->getAllParents();
        return view('chart_of_accounts/form', [
            'mode'    => 'create',
            'parents' => $parents,
        ]);
    }

    /**
     * Simpan akun baru
     */
    public function store()
    {
        $input = $this->request->getPost(['code','name','type', 'subtype', 'normal_balance','parent_id']);
        $this->service->create($input);
        return redirect()->to('/chart-of-accounts')->with('success', 'Account created.');
    }

    /**
     * Form edit akun
     */
    public function edit($id)
    {
        $acct = $this->service->find((int)$id);
        if (! $acct) {
            throw PageNotFoundException::forPageNotFound('Account not found');
        }
        return view('chart_of_accounts/form', [
            'mode'    => 'edit',
            'account' => $acct,
            'parents' => $this->service->getAllParents(),
        ]);
    }

    /**
     * Update akun
     */
    public function update($id)
    {
        $input = $this->request->getPost(['code','name','type','subtype','normal_balance','parent_id']);
        $this->service->update((int)$id, $input);
        return redirect()->to('/chart-of-accounts')->with('success', 'Account updated.');
    }

    /**
     * Soft-delete akun via AJAX
     */
    public function delete($id)
{
    $this->service->delete((int)$id);

    return $this->response->setJSON([
        csrf_token() => csrf_hash(),  // send back the rotated token
        'success'    => true
    ]);
}
}
