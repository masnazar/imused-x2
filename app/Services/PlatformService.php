<?php namespace App\Services;

use App\Models\PlatformModel;
use CodeIgniter\Database\Exceptions\DataException;
use Config\Database;

class PlatformService
{
    protected PlatformModel $model;
    protected $db;

    public function __construct()
    {
        $this->model = new PlatformModel();
        $this->db    = Database::connect();
    }

    /**
     * Ambil data untuk DataTables (pagination + search)
     */
    public function getPaginated(array $params): array
    {
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $draw   = (int) ($params['draw']   ?? 1);

        // ❌ JANGAN pakai builder('p'), tapi:
        $builder = $this->db->table('platforms p')
            ->select('
                p.id,
                p.code,
                p.name,
                p.created_at,
                p.updated_at
            ');

        // 🔍 global search
        if (! empty($params['search']['value'])) {
            $kw = trim($params['search']['value']);
            $builder->groupStart()
                ->like('p.code', $kw)
                ->orLike('p.name', $kw)
                ->groupEnd();
        }

        // 📊 hitung total filtered (tanpa limit)
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // 📦 ambil data page ini
        $data = $builder
            ->orderBy('p.code', 'asc')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => (int) $this->model->countAll(),
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Cari satu platform
     */
    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Buat platform baru
     *
     * @throws DataException
     */
    public function create(array $data): int
    {
        if (empty($data['code'])) {
            throw new DataException('Kode Platform wajib diisi.');
        }
        if (empty($data['name'])) {
            throw new DataException('Nama Platform wajib diisi.');
        }

        $now = date('Y-m-d H:i:s');
        $insert = [
            'code'       => $data['code'],
            'name'       => $data['name'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $id = $this->model->insert($insert);
        if (! $id) {
            throw new DataException('Gagal menyimpan Platform.');
        }
        return (int)$id;
    }

    /**
     * Perbarui platform
     *
     * @throws DataException
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data['code'])) {
            throw new DataException('Kode Platform wajib diisi.');
        }
        if (empty($data['name'])) {
            throw new DataException('Nama Platform wajib diisi.');
        }

        $update = [
            'code'       => $data['code'],
            'name'       => $data['name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $this->model->update($id, $update);
        if (! $ok) {
            throw new DataException('Gagal memperbarui Platform.');
        }
        return $ok;
    }

    /**
     * Hapus platform
     *
     * @throws DataException
     */
    public function delete(int $id): bool
    {
        $ok = $this->model->delete($id);
        if (! $ok) {
            throw new DataException('Gagal menghapus Platform.');
        }
        return $ok;
    }
}
