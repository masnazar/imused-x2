<?php namespace App\Services;

use App\Models\AccountModel;
use CodeIgniter\Database\Exceptions\DataException;
use Config\Database;

class ChartOfAccountsService
{
    protected AccountModel $model;
    protected $db;

    public function __construct()
    {
        $this->model = new AccountModel();
        $this->db    = Database::connect();
    }

    /**
     * Ambil data untuk DataTables (pagination + search + parent join)
     */
    public function getPaginated(array $params): array
    {
        $start  = isset($params['start'])  ? (int)$params['start']  : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $draw   = isset($params['draw'])   ? (int)$params['draw']   : 1;

        // Mulai dari table accounts AS a, join parent sebagai p
        $builder = $this->db
            ->table('accounts AS a')
            ->select("
                a.id,
                a.code,
                a.name,
                a.type,
                a.normal_balance,
                a.parent_id,
                a.created_at,
                a.updated_at,
                p.code   AS parent_code,
                p.name   AS parent_name
            ")
            ->join('accounts AS p', 'p.id = a.parent_id', 'left');

        // Global search
        if (! empty($params['search']['value'])) {
            $kw = $params['search']['value'];
            $builder->groupStart()
                ->like('a.code', $kw)
                ->orLike('a.name', $kw)
                ->orLike('p.code', $kw)
                ->orLike('p.name', $kw)
                ->groupEnd();
        }

        // Hitung jumlah hasil filter tanpa mengambil data
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // Ambil page
        $data = $builder
            ->orderBy('a.code', 'asc')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // Total keseluruhan (tanpa filter)
        $recordsTotal = (int)$this->model->countAll();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Semua akun yang bisa jadi parent
     */
    public function getAllParents(): array
    {
        return $this->model->findAll();
    }

    /**
     * Buat akun baru
     */
    public function create(array $data): int
    {
        // Pastikan parent_id numeric atau null
        $data['parent_id'] = isset($data['parent_id']) && is_numeric($data['parent_id'])
            ? (int)$data['parent_id']
            : null;

        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        $id = $this->model->insert($data);
        if (! $id) {
            throw new DataException('Gagal menyimpan akun.');
        }

        return (int)$id;
    }

    /**
     * Cari satu akun
     */
    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Update akun
     */
    public function update(int $id, array $data): bool
    {
        $data['parent_id'] = isset($data['parent_id']) && is_numeric($data['parent_id'])
            ? (int)$data['parent_id']
            : null;

        $data['updated_at'] = date('Y-m-d H:i:s');

        return (bool)$this->model->update($id, $data);
    }

    /**
     * Soft-delete akun
     */
    public function delete(int $id): bool
    {
        return (bool)$this->model->delete($id);
    }
}
