<?php

namespace App\Services;

use App\Repositories\MarketplaceTransactionRepository;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Service Layer untuk Marketplace Transaction
 */
class MarketplaceTransactionService
{
    protected MarketplaceTransactionRepository $repo;

    public function __construct()
    {
        $this->repo = new MarketplaceTransactionRepository();
    }

    /**
     * Handle DataTables AJAX Server Side
     */
    public function getDataTable(IncomingRequest $request, string $platform): array
    {
        $draw   = (int) $request->getVar('draw');
        $start  = (int) $request->getVar('start');
        $length = (int) $request->getVar('length');
        $search = $request->getVar('search')['value'] ?? '';

        $filters = [
            'brand_id'   => $request->getVar('brand_id'),
            'start_date' => $request->getVar('start_date'),
            'end_date'   => $request->getVar('end_date'),
            'search'     => $search
        ];

        $query = $this->repo->getTransactionQuery($filters, $platform);
        $totalFiltered = $query->countAllResults(false);

        $data = $query->limit($length, $start)->get()->getResult();

        $totalRecords = model('MarketplaceTransactionModel')
            ->where('platform', $platform)
            ->countAllResults();

        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ];
    }

    /**
     * Ambil statistik dashboard
     */
    public function getStatistics(IncomingRequest $request, string $platform): array
    {
        $filters = [
            'brand_id'   => $request->getVar('brand_id'),
            'start_date' => $request->getVar('start_date'),
            'end_date'   => $request->getVar('end_date')
        ];

        return $this->repo->getSummaryStats($filters, $platform);
    }
}
