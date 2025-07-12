<?php namespace App\Services;

use App\Repositories\BrandExpenseRepository;
use CodeIgniter\Database\Exceptions\DataException;
use Config\Database;

class BrandExpenseService
{
    protected BrandExpenseRepository $repo;
    protected $db;

    public function __construct()
    {
        helper('session');
        $this->repo = new BrandExpenseRepository();
        $this->db   = Database::connect();
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
        // validasi
        if (empty($data['date'])) {
            throw new DataException('Tanggal wajib diisi.');
        }
        if (empty($data['account_id'])) {
            throw new DataException('COA wajib dipilih.');
        }
        if (empty($data['brand_id'])) {
            throw new DataException('Brand wajib dipilih.');
        }
        if (! empty($data['store_id']) && ! is_numeric($data['store_id'])) {
            throw new DataException('Toko tidak valid.');
        }
        if (empty($data['amount']) || ! is_numeric(str_replace('.','', $data['amount']))) {
            throw new DataException('Jumlah tidak valid.');
        }

        // bersihkan ribuan
        $data['amount'] = (float) str_replace('.','',$data['amount']);

        // processed_by
        $userId = session()->get('user_id');
        if (! $userId) {
            throw new DataException('User tidak terautentikasi.');
        }
        $data['processed_by'] = $userId;

        // store_id boleh null
        $data['store_id'] = ! empty($data['store_id']) ? (int)$data['store_id'] : null;

        // timestamps
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        // simpan
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
        // validasi serupa create
        if (empty($data['date'])) {
            throw new DataException('Tanggal wajib diisi.');
        }
        if (empty($data['account_id'])) {
            throw new DataException('COA wajib dipilih.');
        }
        if (empty($data['brand_id'])) {
            throw new DataException('Brand wajib dipilih.');
        }
        if (! empty($data['store_id']) && ! is_numeric($data['store_id'])) {
            throw new DataException('Toko tidak valid.');
        }
        if (empty($data['amount']) || ! is_numeric(str_replace('.','',$data['amount']))) {
            throw new DataException('Jumlah tidak valid.');
        }

        $data['amount'] = (float) str_replace('.','',$data['amount']);
        $data['store_id']   = ! empty($data['store_id']) ? (int)$data['store_id'] : null;
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
