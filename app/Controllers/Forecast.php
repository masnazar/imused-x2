<?php

namespace App\Controllers;

use App\Services\ForecastService;
use CodeIgniter\HTTP\ResponseInterface;

class Forecast extends BaseController
{
    protected ForecastService $forecast;

    public function __construct()
    {
        $this->forecast = service('ForecastService');
    }

    public function index(): string
    {
        helper('periode');

        $products = model('ProductModel')->findAll();
        $date_filter = view('partials/date_filter', ['default' => date('m-Y')]);

        return view('forecasting/index', [
            'products'     => $products,
            'date_filter'  => $date_filter,
        ]);
    }

    public function predictSingle(): ResponseInterface
{
    helper('periode');

    $productId     = $this->request->getPost('product_id');
    $periode       = $this->request->getPost('periode');
    $forecastDays  = (int) $this->request->getPost('forecast_days');
    $mode          = $this->request->getPost('recommendation_mode') ?? 'rop_based';
    $multiplier    = (float) $this->request->getPost('multiplier');
    $safetyPercent = (float) $this->request->getPost('safety_stock_percent');


    if (!$periode) {
        return $this->response->setJSON(['error' => 'Periode tidak boleh kosong.']);
    }

    [$startDate, $endDate] = get_date_range_from_periode($periode);

    if (!$startDate || !$endDate) {
        return $this->response->setJSON(['error' => 'Periode tidak valid.']);
    }

    $result = $this->forecast->forecastProductStock(
        $productId, $startDate, $endDate, $forecastDays, $mode, $multiplier, $safetyPercent
    );
    

    return $this->response->setJSON($result);
}

public function predictAll(): ResponseInterface
{
    helper('periode');

    $periode           = $this->request->getPost('periode');
    $forecastDays      = (int) $this->request->getPost('forecast_days');
    $recommendationMode = $this->request->getPost('recommendation_mode') ?? 'hybrid';
    $multiplier        = (float) $this->request->getPost('multiplier') ?: 3.0;

    if (!$periode) {
        return $this->response->setJSON(['error' => 'Periode tidak boleh kosong.']);
    }

    [$startDate, $endDate] = get_date_range_from_periode($periode);
    $products = model('ProductModel')->findAll();

    $results = [];
    foreach ($products as $product) {
        $results[] = $this->forecast->forecastProductStock(
            $product['id'],
            $startDate,
            $endDate,
            $forecastDays,
            $recommendationMode,
            $multiplier,
            (float) $product['safety_stock']
        );
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data'   => $results,
    ]);
}

}
