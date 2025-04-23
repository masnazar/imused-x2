<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

class Customer extends BaseController
{
    public function index(): string
    {
        return view('customers/index');
    }

    public function getData(): ResponseInterface
    {
        $request = service('request');
        $model = new CustomerModel();

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');

        $total = $model->countAll();
        $filtered = $total;

        $data = $model->orderBy('updated_at', 'DESC')->findAll($length, $start);

        return $this->response->setJSON([
            'draw' => (int)$draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }

    public function detail(int $id): string
    {
        $model = new CustomerModel();
        $data = $model->find($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // nanti tambahin histori transaksi dll di sini
        return view('customers/detail', ['customer' => $data]);
    }
}
