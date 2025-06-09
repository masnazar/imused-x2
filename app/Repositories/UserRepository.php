<?php

namespace App\Repositories;

use App\Models\UserModel;
use CodeIgniter\Database\ConnectionInterface;

/**
 * Class UserRepository
 * Repository untuk mengelola data pengguna.
 */
class UserRepository
{
    protected $userModel;
    protected $db;

    /**
     * Constructor.
     *
     * @param ConnectionInterface|null $db
     */
    public function __construct(ConnectionInterface $db = null)
    {
        $this->userModel = new UserModel();
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Cari pengguna berdasarkan email.
     *
     * @param string $email
     * @return object|null
     */
    public function findByEmail($email)
    {
        return $this->userModel
            ->select('users.*, roles.role_name as role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.email', $email)
            ->first();
    }

    /**
     * Cari pengguna berdasarkan reset token.
     *
     * @param string $token
     * @return object|null
     */
    public function findByResetToken($token)
    {
        return $this->userModel->where('reset_token', $token)->first();
    }

    /**
     * Simpan reset token untuk pengguna.
     *
     * @param int $userId
     * @param string $token
     * @return bool
     */
    public function saveResetToken($userId, $token)
    {
        if (empty($userId) || empty($token)) {
            log_message('error', '❌ Gagal menyimpan reset token: User ID atau token kosong.');
            return false;
        }

        log_message('info', '🟢 Menyimpan reset token untuk User ID: ' . $userId . ' | Token: ' . $token);

        $data = ['reset_token' => $token];
        $result = $this->userModel->update($userId, $data);

        if (!$result) {
            log_message('error', '❌ Gagal update reset_token di database.');
        }

        return $result;
    }

    /**
     * Perbarui password pengguna.
     *
     * @param int $userId
     * @param string $hashedPassword
     * @return bool
     */
    public function updatePassword($userId, $hashedPassword)
    {
        return $this->userModel->update($userId, [
            'password' => $hashedPassword,
            'reset_token' => null
        ]);
    }

    /**
     * Buat pengguna baru.
     *
     * @param array $data
     * @return bool
     */
    public function createUser($data)
    {
        log_message('info', '🟢 Data diterima di Repository: ' . json_encode($data));

        $result = $this->userModel->insert($data);

        if (!$result) {
            log_message('error', '🔴 Gagal insert user: ' . json_encode($this->userModel->errors()));
            return false;
        }

        log_message('info', '✅ User berhasil disimpan dengan ID: ' . $this->userModel->insertID());
        return true;
    }

    /**
     * Perbarui data pengguna.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data)
    {
        try {
            log_message('info', '🔥 Data yang dikirim ke Model: ' . print_r($data, true));

            $result = $this->userModel->update($id, $data);

            $lastQuery = $this->userModel->db->getLastQuery();
            log_message('info', '🔍 Query yang dijalankan: ' . $lastQuery);

            if (!$result) {
                log_message('error', '❌ Gagal update user.');
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', '❌ Repository Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil error terakhir dari model.
     *
     * @return array|null
     */
    public function getError()
    {
        return $this->userModel->error();
    }

    /**
     * Ambil pengguna berdasarkan ID.
     *
     * @param int $id
     * @return object|null
     */
    public function getUserById($id)
    {
        return $this->userModel
            ->select('id, name, password, email, role_id, whatsapp as whatsapp_number, birth_date, profile_image, bio')
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    /**
     * Ambil semua izin (permissions).
     *
     * @return array
     */
    public function getAllPermissions()
    {
        return $this->db->table('permissions')
            ->select('permission_name')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil izin berdasarkan role.
     *
     * @param int $roleId
     * @return array
     */
    public function getPermissionsByRole($roleId)
    {
        return $this->db->table('role_permissions')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->select('permissions.permission_name')
            ->get()
            ->getResultArray();
    }

    public function getUserPermissions(int $userId): array
{
    return $this->db->table('user_permissions')
        ->select('p.permission_name')
        ->join('permissions p', 'p.id = user_permissions.permission_id')
        ->where('user_permissions.user_id', $userId)
        ->get()
        ->getResultArray();
}

/**
 * Ambil user lengkap beserta slug role berdasarkan email.
 *
 * @param string $email
 * @return array|null
 */
public function getUserWithRoleSlugByEmail(string $email): ?array
{
    return $this->db->table('users')
        ->select('users.*, roles.role_name, roles.slug as role_slug')
        ->join('roles', 'roles.id = users.role_id', 'left')
        ->where('users.email', $email)
        ->get()
        ->getRowArray();
}


}
