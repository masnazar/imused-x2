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
    $model   = new CustomerModel();

    $draw    = (int) $request->getPost('draw');
    $start   = (int) $request->getPost('start');
    $length  = (int) $request->getPost('length');

    // ambil filter
    $name     = $request->getPost('name');
    $phone    = $request->getPost('phone_number');
    $city     = $request->getPost('city');
    $province = $request->getPost('province');

    // builder DataTable
    $builder = $model->builder();
    if ($name)     $builder->like('name', $name);
    if ($phone)    $builder->like('phone_number', $phone);
    if ($city)     $builder->where('city', $city);
    if ($province) $builder->where('province', $province);

    $recordsFiltered = $builder->countAllResults(false);

    $data = $builder
        ->orderBy('updated_at', 'DESC')
        ->limit($length, $start)
        ->get()
        ->getResult();

    // chart: ambil bulan & tahun untuk 6 bulan terakhir
    $statChart = $model->builder()
        ->select("YEAR(created_at) AS tahun, MONTH(created_at) AS bulan, COUNT(*) AS total")
        ->where('created_at >=', date('Y-m-01', strtotime('-5 months')))
        ->groupBy(['tahun','bulan'])
        ->orderBy('tahun, bulan')
        ->get()
        ->getResult();

    $monthLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $labels = [];
    $series = [];

    foreach ($statChart as $row) {
        $idx = (int)$row->bulan - 1;
        // gabungkan nama bulan + spasi + tahun
        $labels[] = $monthLabels[$idx] . ' ' . $row->tahun;
        $series[] = (int)$row->total;
    }

    $stats = [
        'total_customers' => $model->countAll(),
        'total_ltv'       => array_sum(array_column($data, 'ltv')),
        'chart'           => [
            'labels' => $labels,
            'series' => $series
        ]
    ];

    return $this->response->setJSON([
        'draw'            => $draw,
        'recordsTotal'    => $model->countAll(),
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data,
        'stats'           => $stats
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

public function filters()
{
    $db = db_connect();

    // Ambil daftar kota/kabupaten unik dari customer
    $cityQuery = $db->table('customers')
        ->select('city')
        ->distinct()
        ->where('city IS NOT NULL', null, false)
        ->orderBy('city')
        ->get();

    $cities = array_map(fn($row) => $row->city, $cityQuery->getResult());

    // Ambil daftar provinsi unik dari customer
    $provQuery = $db->table('customers')
        ->select('province')
        ->distinct()
        ->where('province IS NOT NULL', null, false)
        ->orderBy('province')
        ->get();

    $provinces = array_map(fn($row) => $row->province, $provQuery->getResult());

    return $this->response->setJSON([
        'cities' => $cities,
        'provinces' => $provinces
    ]);
}


}
