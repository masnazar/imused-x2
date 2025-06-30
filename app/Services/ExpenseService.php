<?php namespace App\Services;

use App\Repositories\ExpenseRepository;
use App\Models\AccountModel;
use App\Models\BrandModel;
use App\Models\PlatformModel;
use CodeIgniter\Database\Exceptions\DataException;

class ExpenseService
{
    protected ExpenseRepository $repo;
    protected AccountModel       $account;
    protected BrandModel         $brand;
    protected PlatformModel      $platform;

    /** Tipe proses yang diizinkan */
    private array $allowedTypes = [
        'Request',
        'Debit Saldo Akun',
    ];

    public function __construct()
    {
        $this->repo     = new ExpenseRepository();
        $this->account  = new AccountModel();
        $this->brand    = new BrandModel();
        $this->platform = new PlatformModel();
    }

    /**
     * Ambil data untuk DataTables
     */
    public function getPaginated(array $params): array
    {
        return $this->repo->getPaginated($params);
    }

    /**
     * Semua COA
     */
    public function getAllCoa(): array
    {
        return $this->account->findAll();
    }

    /**
     * Semua Brand
     */
    public function getAllBrands(): array
    {
        return $this->brand->findAll();
    }

    /**
     * Semua Platform
     */
    public function getAllPlatforms(): array
    {
        return $this->platform->findAll();
    }

    /**
     * Simpan pengeluaran baru
     *
     * @throws DataException
     */
    public function create(array $data): int
    {
        // ─── Validasi ────────────────────────────────────────────────────────
        if (empty($data['date'])) {
            throw new DataException('Tanggal wajib diisi.');
        }
        if (empty($data['description'])) {
            throw new DataException('Deskripsi wajib diisi.');
        }
        if (empty($data['account_id']) || ! is_numeric($data['account_id'])) {
            throw new DataException('COA wajib dipilih.');
        }
        if (! empty($data['brand_id']) && ! is_numeric($data['brand_id'])) {
            throw new DataException('Brand tidak valid.');
        }
        if (! empty($data['platform_id']) && ! is_numeric($data['platform_id'])) {
            throw new DataException('Platform tidak valid.');
        }
        if (empty($data['type']) || ! in_array($data['type'], $this->allowedTypes, true)) {
            throw new DataException('Tipe proses tidak valid.');
        }
        if (! isset($data['amount']) || ! is_numeric($data['amount'])) {
            throw new DataException('Jumlah tidak valid.');
        }

        // ─── Ambil user dari session ─────────────────────────────────────────
        helper('session');
        $userId = session()->get('user_id');
        if (! $userId) {
            throw new DataException('User tidak terautentikasi.');
        }

        // ─── Siapkan data insert ─────────────────────────────────────────────
        $now = date('Y-m-d H:i:s');
        $insert = [
            'date'          => $data['date'],
            'description'   => $data['description'],
            'account_id'    => (int)$data['account_id'],
            'brand_id'      => ! empty($data['brand_id'])    ? (int)$data['brand_id']    : null,
            'platform_id'   => ! empty($data['platform_id']) ? (int)$data['platform_id'] : null,
            'type'          => $data['type'],
            'amount'        => (float)$data['amount'],
            'processed_by'  => $userId,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        $id = $this->repo->insert($insert);
        if (! $id) {
            throw new DataException('Gagal menyimpan pengeluaran.');
        }

        return $id;
    }

    /**
     * Cari satu pengeluaran
     */
    public function find(int $id): ?array
    {
        return $this->repo->find($id);
    }

    /**
     * Update pengeluaran
     *
     * @throws DataException
     */
    public function update(int $id, array $data): bool
    {
        // (Validasi sama dengan create)
        if (empty($data['date'])) {
            throw new DataException('Tanggal wajib diisi.');
        }
        if (empty($data['description'])) {
            throw new DataException('Deskripsi wajib diisi.');
        }
        if (empty($data['account_id']) || ! is_numeric($data['account_id'])) {
            throw new DataException('COA wajib dipilih.');
        }
        if (! empty($data['brand_id']) && ! is_numeric($data['brand_id'])) {
            throw new DataException('Brand tidak valid.');
        }
        if (! empty($data['platform_id']) && ! is_numeric($data['platform_id'])) {
            throw new DataException('Platform tidak valid.');
        }
        if (empty($data['type']) || ! in_array($data['type'], $this->allowedTypes, true)) {
            throw new DataException('Tipe proses tidak valid.');
        }
        if (! isset($data['amount']) || ! is_numeric($data['amount'])) {
            throw new DataException('Jumlah tidak valid.');
        }

        // ─── Siapkan data update ─────────────────────────────────────────────
        $update = [
            'date'          => $data['date'],
            'description'   => $data['description'],
            'account_id'    => (int)$data['account_id'],
            'brand_id'      => ! empty($data['brand_id'])    ? (int)$data['brand_id']    : null,
            'platform_id'   => ! empty($data['platform_id']) ? (int)$data['platform_id'] : null,
            'type'          => $data['type'],
            'amount'        => (float)$data['amount'],
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $ok = $this->repo->update($id, $update);
        if (! $ok) {
            throw new DataException('Gagal memperbarui pengeluaran.');
        }

        return $ok;
    }

    /**
     * Hapus pengeluaran
     *
     * @throws DataException
     */
    public function delete(int $id): bool
    {
        $ok = $this->repo->delete($id);
        if (! $ok) {
            throw new DataException('Gagal menghapus pengeluaran.');
        }
        return $ok;
    }
}
