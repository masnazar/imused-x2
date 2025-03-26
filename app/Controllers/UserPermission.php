<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PermissionModel;
use CodeIgniter\Controller;

class UserPermission extends Controller
{
    protected $db;
    protected $userModel;
    protected $permissionModel;
    protected $permissionService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
        $this->permissionModel = new PermissionModel();
        $this->permissionService = new \App\Services\PermissionService(); // kalau ada service-nya
    }

    /**
     * 📌 Menampilkan daftar user & assign permission
     */
    public function index()
    {
        // Cek role admin
        if (session('role_id') != 1) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak!');
        }

        $users = (new UserModel())->findAll();
        $permissions = (new PermissionModel())->findAll();

        return view('permissions/assign_user', compact('users', 'permissions'));
    }

    /**
     * 📌 Ambil permission user via AJAX
     */
    public function getUserPermissions($userId)
    {
        $assigned = $this->db->table('user_permissions')
            ->select('permission_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return $this->response->setJSON(array_column($assigned, 'permission_id'));
    }

    /**
     * 📌 Simpan permission yang dipilih
     */
    public function assign()
{
    if (!$this->request->is('post')) {
        return redirect()->back()->with('error', 'Metode tidak valid');
    }

    $userId = $this->request->getPost('user_id');
    $permissions = $this->request->getPost('permissions') ?? [];

    // 🔥 Hapus permission lama
    $this->db->table('user_permissions')->where('user_id', $userId)->delete();

    // ✅ Simpan permission baru
    $data = [];
    foreach ($permissions as $permId) {
        $data[] = [
            'user_id' => $userId,
            'permission_id' => $permId,
        ];
    }

    if (!empty($data)) {
        $this->db->table('user_permissions')->insertBatch($data);
    }

    // ✅ Ambil ulang data untuk view
    $users = $this->userModel->findAll();
    $allPermissions = $this->permissionModel->findAll();
    $userPermissions = $this->permissionService->getPermissionsByUserId($userId);

    // ⛔ Log debugging sementara
    log_message('debug', '👤 User ID dipilih: ' . $userId);
    log_message('debug', '✅ Permissions yang dipilih: ' . json_encode($userPermissions));

    return view('permissions/assign_user', [
        'users' => $users,
        'permissions' => $allPermissions,
        'selectedUserId' => $userId,
        'userPermissions' => $userPermissions,
    ]);
}



}
