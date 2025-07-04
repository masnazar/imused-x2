<?php namespace App\Repositories;

use Config\Database;
use CodeIgniter\Database\BaseBuilder; 

class ExpenseRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Builder dasar untuk table `expenses`
     */
    protected function getBaseBuilder(): BaseBuilder
    {
        return $this->db->table('expenses e')
            ->join('accounts   a', 'a.id = e.account_id',    'left')
            ->join('brands     b', 'b.id = e.brand_id',      'left')
            ->join('platforms  p', 'p.id = e.platform_id',   'left')
            ->join('users      u', 'u.id = e.processed_by',  'left');
    }

    /**
     * DataTables pagination + filter + join
     */
    public function getPaginated(array $params): array
    {
        helper('periode');

        // --- 1) Hitung rentang tanggal ---
        $startDate = null;
        $endDate   = null;
        if (($params['jenis_filter'] ?? '') === 'periode' && ! empty($params['periode'])) {
            list($startDate, $endDate) = get_date_range_from_periode($params['periode']);
        } elseif (
            ($params['jenis_filter'] ?? '') === 'custom'
            && ! empty($params['start_date'])
            && ! empty($params['end_date'])
        ) {
            $startDate = $params['start_date'];
            $endDate   = $params['end_date'];
        }

        // --- 2) Build query utama ---
        $builder = $this->db->table('expenses e')
            ->select([
                'e.id', 'e.date', 'e.description',
                'a.code   AS coa_code',    'a.name   AS coa_name',
                'b.brand_name',
                'p.code   AS platform_code','p.name   AS platform_name',
                'e.type', 'u.name   AS processed_by',
                'e.amount','e.created_at','e.updated_at',
            ])
            ->join('accounts   a','a.id = e.account_id','left')
            ->join('brands     b','b.id = e.brand_id',    'left')
            ->join('platforms  p','p.id = e.platform_id','left')
            ->join('users      u','u.id = e.processed_by','left');

        // --- 3) Terapkan filter tanggal jika ada ---
        if ($startDate && $endDate) {
            $builder->where('e.date >=', $startDate)
                    ->where('e.date <=', $endDate);
        }

        // --- 4) Global search DataTables ---
        if (! empty($params['search']['value'])) {
            $kw = trim($params['search']['value']);
            $builder->groupStart()
                ->like('e.description', $kw)
                ->orLike('a.code',        $kw)
                ->orLike('b.brand_name',  $kw)
                ->orLike('p.name',        $kw)
                ->orLike('u.name',        $kw)
                ->groupEnd();
        }

        // --- 5) Hitung recordsFiltered ---
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // --- 6) Ambil pagination params ---
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $draw   = (int) ($params['draw']   ?? 1);

        // --- 7) Ambil data page ---
        $data = $builder
            ->orderBy('e.date','desc')
            ->limit($length,$start)
            ->get()
            ->getResultArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => (int) $this->db->table('expenses')->countAllResults(),
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Ambil statistik (count, total, avg) dan % delta vs periode sebelumnya
     */
    public function getStatistics(array $params): array
    {
        helper('periode');

        // 1) Tangkap rentang filter
        [$start, $end] = [null, null];
        if (($params['jenis_filter'] ?? '') === 'periode' && ! empty($params['periode'])) {
            [$start, $end] = get_date_range_from_periode($params['periode']);
        } elseif (
            ($params['jenis_filter'] ?? '') === 'custom'
            && ! empty($params['start_date'])
            && ! empty($params['end_date'])
        ) {
            $start = $params['start_date'];
            $end   = $params['end_date'];
        }

        // 2) Hitung periode "current"
        $baseCurr = clone $this->getBaseBuilder();
        if ($start && $end) {
            $baseCurr->where('e.date >=', $start)
                     ->where('e.date <=', $end);
        }

        // a) count
        $cloneCount   = clone $baseCurr;
        $currentCount = (int) $cloneCount->countAllResults(false);

        // b) total & avg
        $row          = $baseCurr
            ->selectSum('e.amount','total')
            ->get()
            ->getRow();
        $currentTotal = (float) ($row->total ?? 0);
        $currentAvg   = $currentCount ? $currentTotal / $currentCount : 0;

        // 3) Hitung periode "previous" dengan panjang sama
        $prevCount = $prevTotal = 0;
        if ($start && $end) {
            $diffDays  = (new \DateTime($end))
                ->diff(new \DateTime($start))
                ->days + 1;

            $prevEnd   = (new \DateTime($start))->modify('-1 day')->format('Y-m-d');
            $prevStart = (new \DateTime($start))
                ->modify("-{$diffDays} days")->format('Y-m-d');

            $basePrev  = clone $this->getBaseBuilder();
            $basePrev->where('e.date >=', $prevStart)
                     ->where('e.date <=', $prevEnd);

            $clonePrev = clone $basePrev;
            $prevCount = (int) $clonePrev->countAllResults(false);

            $row2      = $basePrev
                ->selectSum('e.amount','total')
                ->get()
                ->getRow();
            $prevTotal = (float) ($row2->total ?? 0);
        }

        // 4) Hitung % delta (null kalau prev=0)
        // Hitung prevAvg
        $prevAvg = $prevCount
            ? $prevTotal / $prevCount
            : 0;

        // 4) Hitung % delta (null kalau prev=0)
        $pctCount = $prevCount
            ? ($currentCount - $prevCount) / $prevCount * 100
            : null;
        $pctTotal = $prevTotal
            ? ($currentTotal - $prevTotal) / $prevTotal * 100
            : null;
        $pctAvg   = $prevAvg
            ? ($currentAvg   - $prevAvg)   / $prevAvg   * 100
            : null;

        // Return tambahkan pct_avg
        return [
            'count'     => $currentCount,
            'total'     => $currentTotal,
            'avg'       => $currentAvg,
            'pct_count' => $pctCount,
            'pct_total' => $pctTotal,
            'pct_avg'   => $pctAvg,
        ];
    }

    /**
     * Breakdown total pengeluaran per COA
     */
    public function getCostByCoa(array $params): array
    {
        // ambil builder & filter tanggal sama seperti getStatistics
        helper('periode');
        [$start, $end] = [null, null];
        if (($params['jenis_filter'] ?? '') === 'periode' && $params['periode']) {
            [$start, $end] = get_date_range_from_periode($params['periode']);
        } elseif (($params['jenis_filter'] ?? '') === 'custom'
            && $params['start_date'] && $params['end_date']) {
            $start = $params['start_date'];
            $end   = $params['end_date'];
        }

        $b = clone $this->getBaseBuilder();
        if ($start && $end) {
            $b->where('e.date >=', $start)
              ->where('e.date <=', $end);
        }

        return $b
            ->select('a.name AS coa_name')
            ->selectSum('e.amount', 'total')
            ->groupBy('a.id')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Breakdown total pengeluaran per Platform
     */
    public function getCostByPlatform(array $params): array
    {
        // logika persis seperti di atas, cuma groupBy p.id
        helper('periode');
        [$start, $end] = [null, null];
        if (($params['jenis_filter'] ?? '') === 'periode' && $params['periode']) {
            [$start, $end] = get_date_range_from_periode($params['periode']);
        } elseif (($params['jenis_filter'] ?? '') === 'custom'
            && $params['start_date'] && $params['end_date']) {
            $start = $params['start_date'];
            $end   = $params['end_date'];
        }

        $b = clone $this->getBaseBuilder();
        if ($start && $end) {
            $b->where('e.date >=', $start)
              ->where('e.date <=', $end);
        }

        return $b
            ->select('p.name AS platform_name')
            ->selectSum('e.amount', 'total')
            ->groupBy('p.id')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Ambil satu expense (untuk form edit/view)
     */
    public function find(int $id): ?array
    {
        return $this->db->table('expenses e')
            ->select([
                'e.*',
                'a.code        AS coa_code',
                'a.name        AS coa_name',
                'b.brand_name',
                'p.code        AS platform_code',
                'p.name        AS platform_name',
                'u.name        AS processed_by',
            ])
            ->join('accounts   a', 'a.id = e.account_id',    'left')
            ->join('brands     b', 'b.id = e.brand_id',      'left')
            ->join('platforms  p', 'p.id = e.platform_id',   'left')
            ->join('users      u', 'u.id = e.processed_by',  'left')
            ->where('e.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Insert baru
     */
    public function insert(array $data): int
    {
        $this->db->table('expenses')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Update
     */
    public function update(int $id, array $data): bool
    {
        return (bool) $this->db->table('expenses')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Hapus
     */
    public function delete(int $id): bool
    {
        return (bool) $this->db->table('expenses')
            ->where('id', $id)
            ->delete();
    }
}
