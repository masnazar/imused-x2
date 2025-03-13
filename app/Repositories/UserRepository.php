<?php

namespace App\Repositories;

use App\Models\UserModel;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class UserRepository
{
    protected $userModel;
    protected $db;

    public function __construct(ConnectionInterface $db = null)
    {
        $this->userModel = new UserModel();
        $this->db = $db ?? \Config\Database::connect(); // ✅ Pastikan pakai `\Config\Database`
    }

    public function findByEmail($email)
    {
        return $this->userModel
            ->select('users.*, roles.role_name as role_name') // 🔥 Ambil role_name juga
            ->join('roles', 'roles.id = users.role_id', 'left') // 🔥 Join ke tabel roles
            ->where('users.email', $email)
            ->first();
    }


    public function findByResetToken($token)
    {
        return $this->userModel->where('reset_token', $token)->first();
    }

    public function saveResetToken($userId, $token)
    {
        if (empty($userId) || empty($token)) {
            log_message('error', '❌ Gagal menyimpan reset token: User ID atau token kosong.');
            return false;
        }

        // Debugging
        log_message('info', '🟢 Menyimpan reset token untuk User ID: ' . $userId . ' | Token: ' . $token);

        // Pastikan reset_token diubah dengan nilai baru
        $data = ['reset_token' => $token];

        $result = $this->userModel->update($userId, $data);
        if (!$result) {
            log_message('error', '❌ Gagal update reset_token di database.');
        }

        return $result;
    }


    public function updatePassword($userId, $hashedPassword)
    {
        return $this->userModel->update($userId, [
            'password' => $hashedPassword,
            'reset_token' => null // Hapus token setelah reset berhasil
        ]);
    }

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

    public function updateUser($id, $data)
{
    try {
        log_message('info', '🔥 Data yang dikirim ke Model: ' . print_r($data, true));

        $result = $this->userModel->update($id, $data);

        // Cek apakah query dijalankan
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

public function getError()
{
    return $this->userModel->error();
}

    public function getUserById($id)
    {
        return $this->userModel
            ->select('id, name, password, email, role_id, whatsapp as whatsapp_number, birth_date, profile_image, bio')
            ->where('id', $id)
            ->get()
            ->getRow(); // Mengembalikan object, bukan array
    }

    public function getAllPermissions()
{
    return $this->db->table('permissions')
        ->select('permission_name')
        ->get()
        ->getResultArray();
}

public function getPermissionsByRole($roleId)
{
    return $this->db->table('role_permissions')
        ->join('permissions', 'permissions.id = role_permissions.permission_id')
        ->where('role_permissions.role_id', $roleId)
        ->select('permissions.permission_name')
        ->get()
        ->getResultArray();
}

    
}
