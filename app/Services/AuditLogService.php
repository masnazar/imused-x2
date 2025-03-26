<?php

namespace App\Services;

use App\Models\AuditLogModel;

class AuditLogService
{
    protected $auditLogModel;

    public function __construct()
    {
        $this->auditLogModel = new AuditLogModel();
    }

    public function log(
        int $userId = null,
        string $model,
        int $modelId,
        string $action,
        $oldData = null,
        $newData = null
    ): bool {
        try {
            $data = [
                'user_id'    => $userId,
                'model'      => $model,
                'model_id'   => $modelId,
                'activity'   => $action, // Map ke kolom 'activity'
                'old_data'   => $oldData ? json_encode($oldData, JSON_HEX_APOS | JSON_HEX_QUOT) : null,
                'new_data'   => $newData ? json_encode($newData, JSON_HEX_APOS | JSON_HEX_QUOT) : null,
                'ip_address' => service('request')->getIPAddress(),
                'user_agent' => substr(service('request')->getUserAgent(), 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            log_message('debug', 'Audit Log Data: ' . print_r($data, true));
            // Explicitly specify the column names in the insert query
            return $this->auditLogModel->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Audit Log Error: ' . $e->getMessage());
            return false;
        }
    }

    public function getLogsForModel(string $model, int $modelId)
    {
        return $this->auditLogModel
            ->where('model', $model)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}