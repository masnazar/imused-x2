<?php

namespace App\Services;

use App\Repositories\MarketplaceTransactionRepository;
use App\Repositories\ProductRepository;
use CodeIgniter\HTTP\CURLRequest;

class ForecastService
{
    protected MarketplaceTransactionRepository $transactionRepo;
    protected ProductRepository $productRepo;
    protected CURLRequest $client;

    public function __construct(
        MarketplaceTransactionRepository $transactionRepo,
        ProductRepository $productRepo
    ) {
        $this->transactionRepo = $transactionRepo;
        $this->productRepo = $productRepo;
        $this->client = service('curlrequest');
    }

    /**
     * Proses forecast stok berdasarkan produk dan range tanggal
     *
     * @param int $productId
     * @param string $startDate
     * @param string $endDate
     * @param int $forecastDays
     * @param string $mode
     * @return array
     */
    public function forecastProductStock(
        int $productId,
        string $startDate,
        string $endDate,
        int $forecastDays,
        string $mode = 'hybrid',
        float $multiplier = 3.0,
        float $safetyPercent = 10.0
    ): array {
        $product = $this->productRepo->findById($productId);
        if (!$product) {
            return ['error' => 'Produk tidak ditemukan'];
        }
    
        $historicalSales = $this->transactionRepo->getHistoricalSales($productId, $startDate, $endDate);
    
        $payload = [
            'sku'                   => $product['sku'],
            'sales_data'            => $historicalSales,
            'lead_time_days'        => (int) $product['lead_time_days'],
            'current_stock'         => (int) $product['stock'],
            'forecast_days'         => $forecastDays,
            'recommendation_mode'   => $mode,
            'multiplier'            => $multiplier,
            'safety_stock_percent'  => $safetyPercent
        ];
        
    
        return $this->predict($payload);
    }
    

    /**
     * Kirim data ke Python API
     */
    public function predict(array $payload): array
    {
        try {
            $response = $this->client->post('http://localhost:8000/forecast', [
                'headers'     => ['Content-Type' => 'application/json'],
                'json'        => $payload,
                'http_errors' => false
            ]);

            $data = json_decode($response->getBody(), true);
            return $data ?? ['error' => 'Invalid response format'];
        } catch (\Throwable $e) {
            log_message('error', '[❌ Forecasting Error] ' . $e->getMessage());
            return ['error' => 'Forecasting failed: ' . $e->getMessage()];
        }
    }
}
