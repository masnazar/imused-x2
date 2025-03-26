<?php

namespace App\Services;

use App\Models\AuditLogModel;

/**
 * Service untuk mencatat log audit ke dalam database.
 */
class AuditLogService
{
    /**
     * @var AuditLogModel Model untuk tabel audit log.
     */
    protected $auditLogModel;

    /**
     * Konstruktor untuk inisialisasi model AuditLogModel.
     */
    public function __construct()
    {
        $this->auditLogModel = new AuditLogModel();
    }

    /**
     * Mencatat log audit ke dalam database.
     *
     * @param int|null $userId ID pengguna yang melakukan aksi (opsional).
     * @param string $model Nama model yang terkait dengan log.
     * @param int $modelId ID dari model yang terkait.
     * @param string $action Aksi yang dilakukan (misalnya: 'create', 'update', 'delete').
     * @param mixed|null $oldData Data lama sebelum perubahan (opsional).
     * @param mixed|null $newData Data baru setelah perubahan (opsional).
     * @return bool True jika log berhasil disimpan, false jika gagal.
     */
    public function log(
        int $userId = null,
        string $model,
        int $modelId,
        string $action,
        $oldData = null,
        $newData = null
    ): bool {
        try {
            // Data yang akan disimpan ke tabel audit log
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

            // Log data untuk debugging
            log_message('debug', 'Audit Log Data: ' . print_r($data, true));

            // Simpan data ke database
            return $this->auditLogModel->insert($data);
        } catch (\Exception $e) {
            // Log error jika terjadi kesalahan
            log_message('error', 'Audit Log Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil log audit berdasarkan model dan ID model.
     *
     * @param string $model Nama model yang terkait dengan log.
     * @param int $modelId ID dari model yang terkait.
     * @return array Daftar log audit yang ditemukan.
     */
    public function getLogsForModel(string $model, int $modelId)
    {
        return $this->auditLogModel
            ->where('model', $model)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}