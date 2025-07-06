<?php namespace App\Services;

use App\Repositories\BrandExpenseRepository;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class BrandExpenseService
{
    protected BrandExpenseRepository $repo;
    protected ConnectionInterface     $db;

    public function __construct()
    {
        $this->repo = new BrandExpenseRepository();
        $this->db   = Database::connect();
        helper('session');
    }

    public function getPaginated(array $params): array
    {
        return $this->repo->getPaginated($params);
    }

    public function find(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function create(array $data): int
    {
        // validasi minimal
        if (empty($data['date'])) {
            throw new DataException('Tanggal wajib diisi.');
        }
        if (empty($data['account_id'])) {
            throw new DataException('COA wajib dipilih.');
        }
        if (empty($data['brand_id'])) {
            throw new DataException('Brand wajib dipilih.');
        }
        if (empty($data['amount']) || ! is_numeric(str_replace('.','',$data['amount']))) {
            throw new DataException('Jumlah tidak valid.');
        }

        // bersihkan ribuan
        $data['amount'] = (float) str_replace('.','',$data['amount']);

        // isi processed_by dari session
        $userId = session()->get('user_id');
        if (! $userId) {
            throw new DataException('User tidak terautentikasi.');
        }
        $data['processed_by'] = $userId;

        // timestamps
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        // transaksi
        $this->db->transStart();
            $id = $this->repo->insert($data);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new DataException('Gagal menyimpan Brand Expense.');
        }

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        // validasi mirip create...
        if (empty($data['date'])) {
            throw new DataException('Tanggal wajib diisi.');
        }
        if (empty($data['account_id'])) {
            throw new DataException('COA wajib dipilih.');
        }
        if (empty($data['brand_id'])) {
            throw new DataException('Brand wajib dipilih.');
        }
        if (empty($data['amount']) || ! is_numeric(str_replace('.','',$data['amount']))) {
            throw new DataException('Jumlah tidak valid.');
        }

        // bersihkan
        $data['amount'] = (float) str_replace('.','',$data['amount']);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->transStart();
            $ok = $this->repo->update($id, $data);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new DataException('Gagal memperbarui Brand Expense.');
        }

        return $ok;
    }

    public function delete(int $id): bool
    {
        $this->db->transStart();
            $ok = $this->repo->delete($id);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new DataException('Gagal menghapus Brand Expense.');
        }

        return $ok;
    }
}
