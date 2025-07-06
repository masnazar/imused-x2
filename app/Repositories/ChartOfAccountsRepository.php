<?php namespace App\Repositories;

use Config\Database;
use CodeIgniter\Database\BaseBuilder;

class ChartOfAccountsRepository
{
    protected $db;
    protected $table = 'accounts';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /** Query dasar dengan join parent */
    public function getBaseQuery(): BaseBuilder
    {
        return $this->db->table("{$this->table} a")
            ->select('
                a.id, a.code, a.name, a.type, a.subtype, a.normal_balance,
                p.code AS parent_code, p.name AS parent_name,
                a.created_at, a.updated_at
            ')
            ->join('accounts p', 'p.id = a.parent_id', 'left');
    }

    public function countAll(): int
    {
        return $this->db->table($this->table)
                        ->where('deleted_at', null)
                        ->countAllResults();
    }

    public function countFiltered(BaseBuilder $b): int
    {
        return $b->where('a.deleted_at', null)
                 ->countAllResults(false);
    }
}
