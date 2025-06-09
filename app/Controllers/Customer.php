<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 🎯 Controller untuk manajemen data Customer
 */
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
            'draw' => (int) $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }

    public function detail($id)
    {
        $customer = model(CustomerModel::class)->find($id);

        if (!$customer) {
            return redirect()->to(base_url('customers'))->with('error', 'Customer tidak ditemukan.');
        }

        return view('customers/detail', ['customer' => $customer]);
    }

    /**
     * 🚚 Server-side DataTables: Riwayat Transaksi Customer
     */
    public function history($id)
    {
        $customer = model(CustomerModel::class)->find($id);

        if (!$customer) {
            return $this->response->setJSON([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $params = $this->request->getPost();
        $repo = new \App\Repositories\SoscomTransactionRepository();
        $history = $repo->getTransactionsByPhone($customer['phone_number'], $params);

        return $this->response->setJSON($history);
    }

   public function update($id)
{
    $model = new CustomerModel();
    $customer = $model->find($id);

    if (!$customer) {
        return redirect()->to(base_url('customers'))->with('error', 'Customer tidak ditemukan.');
    }

    log_message('debug', '🧾 Form Data: ' . json_encode($this->request->getPost()));

    $rules = [
        'name'        => 'required|string|max_length[100]',
        'address'     => 'permit_empty|string|max_length[255]',
        'province_id' => 'required|string',
        'city_id'     => 'required|string',
        'district_id' => 'permit_empty|string',
        'village_id'  => 'permit_empty|string',
        'postal_code' => 'permit_empty|string|max_length[10]',
        'dob'         => 'permit_empty|valid_date',
        'gender'      => 'required|in_list[L,P]',
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Validasi gagal.')
            ->with('errors', $this->validator->getErrors());
    }

    $provinceId = $this->request->getPost('province_id');
    $cityId     = $this->request->getPost('city_id');

    $provinceName = $this->getRegionName('provinces', $provinceId);
    $cityName     = $this->getRegionName('regencies', $cityId); // ✅ FIXED: regencies bukan cities

    $data = [
        'name'        => $this->request->getPost('name'),
        'gender'      => $this->request->getPost('gender'),
        'dob'         => $this->request->getPost('dob'),
        'address'     => $this->request->getPost('address'),
        'province_id' => $provinceId,
        'province'    => $provinceName,
        'city_id'     => $cityId,
        'city'        => $cityName,
        'district_id' => $this->request->getPost('district_id') ?: null,
        'village_id'  => $this->request->getPost('village_id') ?: null,
        'postal_code' => $this->request->getPost('postal_code'),
        'updated_at'  => date('Y-m-d H:i:s'),
    ];

    if (!$model->update($id, $data)) {
        log_message('error', '❌ Gagal update customer: ' . json_encode($model->errors()));
        return redirect()->back()->with('error', 'Gagal update.')->withInput();
    }

    return redirect()->to(base_url("customers/detail/$id"))
        ->with('success', 'Profil customer berhasil diperbarui.');
}


/**
 * 🔧 Ambil nama wilayah berdasarkan ID dari tabel lokal
 */
private function getRegionName(string $table, string $id): ?string
{
    try {
        $row = db_connect()
            ->table($table)
            ->select('name')
            ->where('id', $id)
            ->get()
            ->getRow();

        return $row ? $row->name : null;
    } catch (\Throwable $e) {
        log_message('error', "❌ Gagal ambil nama wilayah dari tabel $table untuk ID $id: " . $e->getMessage());
        return null;
    }
}

    public function edit($id)
{
    $model = new CustomerModel();
    $customer = $model->find($id);

    if (!$customer) {
        return redirect()->to(base_url('customers'))->with('error', 'Customer tidak ditemukan.');
    }

    // Ambil nama wilayah dari model/reference table
    $regionModel = model(\App\Models\RegionModel::class); // Buat model kalau belum ada

    $customer['province'] = $regionModel->getProvinceName($customer['province_id']);
    $customer['city']     = $regionModel->getCityName($customer['city_id']);
    $customer['district'] = $regionModel->getDistrictName($customer['district_id']);
    $customer['village']  = $regionModel->getVillageName($customer['village_id']);

    return view('customers/edit', [
        'customer' => $customer
    ]);
}

}
