<?php

namespace App\Services;

use App\Repositories\SoscomTransactionRepository;
use App\Models\CustomerModel;

class SoscomTransactionService
{
    protected SoscomTransactionRepository $repo;

    public function __construct()
    {
        $this->repo = new SoscomTransactionRepository();
    }

    /**
     * Statistik transaksi Soscom
     */
    public function getStatistics(array $filters): array
    {
        return $this->repo->getSummaryStats($filters);
    }

    /**
     * Validasi transaksi baru
     */
    public function getValidationRules(): array
    {
        return [
            'order_number'   => 'required|string|max_length[100]',
            'date'           => 'required|valid_date',
            'brand_id'       => 'required|numeric',
            'selling_price'  => 'required|decimal',
            'hpp'            => 'required|decimal',
            'payment_method' => 'required|in_list[COD,Transfer]',
            'phone_number'   => 'required|regex_match[/^62[0-9]{9,13}$/]'
        ];
    }

    /**
     * Buat transaksi baru Soscom
     */
    public function createTransaction(array $input): int
{
    $customerModel = model('App\Models\CustomerModel');
    $txnModel = model('App\Models\SoscomTransactionModel');

    // 🔁 Cek apakah customer sudah ada
    $existing = $customerModel->where('phone_number', $input['phone_number'])->first();

    if (!$existing) {
        $customerId = $customerModel->insert([
            'name' => $input['name'],
            'phone_number' => $input['phone_number'],
            'city' => $input['city'],
            'province' => $input['province'],
            'order_count' => 1,
            'ltv' => $input['selling_price'],
            'first_order_date' => $input['date'],
            'last_order_date' => $input['date'],
            'segment' => 'New Buyer'
        ]);
    } else {
        $customerId = $existing['id'];

        // 🧠 Update LTV, Last Order & Count
        $customerModel->update($customerId, [
            'order_count' => $existing['order_count'] + 1,
            'ltv' => $existing['ltv'] + $input['selling_price'],
            'last_order_date' => $input['date'],
            'segment' => 'Loyal Buyer' // Bisa pakai rule segmentasi lebih lanjut nanti
        ]);
    }

    $input['customer_id'] = $customerId;

    $txnModel->insert($input);
    return $txnModel->getInsertID();
}


    /**
     * Ambil semua produk di dalam transaksi
     */
    public function getTransactionProducts(int $transactionId): array
    {
        return $this->repo->getTransactionProducts($transactionId);
    }

    /**
     * Detail transaksi
     */
    public function getTransactionDetail(int $id): array
    {
        return $this->repo->getTransactionDetail($id);
    }

    /**
     * Soft delete transaksi
     */
    public function deleteTransaction(int $id): bool
    {
        return model('App\Models\SoscomTransactionModel')->delete($id);
    }

    /**
     * Server-side datatables
     */
    public function getPaginatedTransactions(array $params): array
    {
        return $this->repo->getPaginatedTransactions($params);
    }

    /**
     * Cek apakah customer baru atau bukan
     */
    private function getOrCreateCustomer(array $data): int
    {
        $model = new CustomerModel();

        $existing = $model->where('phone_number', $data['phone_number'])->first();

        if ($existing) {
            return $existing['id'];
        }

        $model->insert([
            'name'             => $data['customer_name'],
            'phone_number'     => $data['phone_number'],
            'city'             => $data['city'],
            'province'         => $data['province'],
            'order_count'      => 1,
            'ltv'              => $data['net_revenue'],
            'first_order_date' => $data['date'],
            'last_order_date'  => $data['date'],
            'segment'          => 'New Customer'
        ]);

        return $model->getInsertID();
    }

    private function insertOrUpdateCustomer(array $data): int
{
    $model = model('App\Models\CustomerModel');
    $existing = $model->where('phone_number', $data['phone_number'])->first();

    if ($existing) {
        return (int)$existing['id'];
    }

    $model->insert([
        'name'            => $data['customer_name'],
        'phone_number'    => $data['phone_number'],
        'city'            => $data['city'],
        'province'        => $data['province'],
        'order_count'     => 1,
        'ltv'             => $data['selling_price'],
        'first_order_date'=> $data['date'],
        'last_order_date' => $data['date'],
        'segment'         => 'New',
    ]);

    return $model->getInsertID();
}

}
