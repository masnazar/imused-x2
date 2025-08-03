<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MarketplaceTransactionModel;

/**
 * Controller khusus untuk pelacakan status pengiriman.
 */
class MarketplaceTransactionTracking extends BaseController
{
    use ResponseTrait;

    /**
     * Melacak status resi menggunakan API eksternal dan memperbarui transaksi.
     */
    public function trackResi(): ResponseInterface
    {
        $request = service('request');
        $courier = $request->getPost('courier');
        $awb     = $request->getPost('awb');

        $apiKey = env('BINDERBYTE_API_KEY');

        $url = "https://api.binderbyte.com/v1/track?api_key={$apiKey}&courier={$courier}&awb={$awb}";

        try {
            $client = \Config\Services::curlrequest();
            $response = $client->get($url);

            $result = json_decode($response->getBody(), true);

            if ($result['status'] !== 200) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Resi tidak ditemukan.'
                ]);
            }

            $summary = $result['data']['summary'] ?? [];
            $lastStatus = strtolower($summary['status'] ?? '');
            $newStatus = 'Dalam Perjalanan';

            if (str_contains($lastStatus, 'delivered')) {
                $newStatus = 'Terkirim';
            } elseif (str_contains($lastStatus, 'return')) {
                $newStatus = 'Returned';
            }

            $model = new MarketplaceTransactionModel();

            $model->where('tracking_number', $awb)
                  ->orWhere('tracking_number', strtoupper($awb))
                  ->orWhere('tracking_number', strtolower($awb))
                  ->set([
                      'status'               => $newStatus,
                      'last_tracking_data'   => json_encode($result['data']),
                      'last_tracking_status' => strtoupper($summary['status'] ?? '-')
                  ])
                  ->update();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result['data'],
                csrf_token() => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[MarketplaceTransactionTracking::trackResi] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghubungi API.',
                csrf_token() => csrf_hash()
            ]);
        }
    }

    /**
     * Memperbarui status resi secara manual.
     */
    public function updateResiStatus(string $platform, int $id): ResponseInterface
    {
        try {
            $status = $this->request->getPost('status');
            $model  = new MarketplaceTransactionModel();
            $model->update($id, ['status' => $status]);

            return $this->response->setJSON([
                'status' => 'success',
                csrf_token() => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[MarketplaceTransactionTracking::updateResiStatus] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui status resi.',
                csrf_token() => csrf_hash()
            ])->setStatusCode(500);
        }
    }
}

