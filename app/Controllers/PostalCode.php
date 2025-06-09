<?php

namespace App\Controllers;

use App\Services\PostalCodeService;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class PostalCode extends BaseController
{
    protected $postalService;

    public function __construct()
    {
        $this->postalService = new PostalCodeService();
    }

    public function index(): string
    {
        $data = [
            'updated' => $this->postalService->countUpdated(),
            'not_updated' => $this->postalService->countNotUpdated(),
            'log' => session()->getFlashdata('log') ?? null,
            'message' => session()->getFlashdata('message') ?? null,
        ];

        return view('postal_code/index', $data);
    }

    public function runBatch()
{
    $result = $this->postalService->runBatch();

    return redirect()->back()->with('message', "{$result['updated']} desa berhasil diupdate.")
                               ->with('list', $result['list']);
}

public function list()
{
    return view('postal_code/list');
}

public function ajaxList()
{
    $request = service('request');
    $db      = db_connect();
    $builder = $db->table('villages v')
        ->select('p.name AS province, r.name AS regency, d.name AS district, v.name AS village, v.postal_code')
        ->join('districts d', 'v.district_id = d.id')
        ->join('regencies r', 'd.regency_id = r.id')
        ->join('provinces p', 'r.province_id = p.id');

    // Server-side search
    $search = $request->getGet('search')['value'] ?? '';
    if ($search) {
        $builder->groupStart()
            ->like('v.name', $search)
            ->orLike('d.name', $search)
            ->orLike('r.name', $search)
            ->orLike('p.name', $search)
            ->orLike('v.postal_code', $search)
            ->groupEnd();
    }

    $totalFiltered = $builder->countAllResults(false);
    $start = $request->getGet('start') ?? 0;
    $length = $request->getGet('length') ?? 10;
    $builder->limit($length, $start);

    $data = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'draw' => (int) $request->getGet('draw'),
        'recordsTotal' => $db->table('villages')->countAll(),
        'recordsFiltered' => $totalFiltered,
        'data' => $data
    ]);
}
}
