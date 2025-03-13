<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PermissionService;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\RolePermissionModel;


class Permission extends BaseController
{
    protected $permissionService;

    public function __construct()
    {
        $this->permissionService = new PermissionService(); // ✅ Fix: Inisialisasi langsung
    }

    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();
        return view('permissions/index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions/create');
    }

    public function store()
    {
        if ($this->validate([
            'permission_name' => 'required|is_unique[permissions.permission_name]',
            'alias' => 'required'
        ])) {
            $this->permissionService->createPermission($this->request->getPost());
            return redirect()->route('permissions.index')->with('success', 'Permission berhasil ditambahkan!');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan permission.');
    }

    public function edit($id)
    {
        $permission = $this->permissionService->getPermissionById($id);
        return view('permissions/edit', compact('permission'));
    }

    public function update($id)
    {
        if ($this->validate([
            'permission_name' => "required|is_unique[permissions.permission_name,id,$id]",
            'alias' => 'required'
        ])) {
            $this->permissionService->updatePermission($id, $this->request->getPost());
            return redirect()->route('permissions.index')->with('success', 'Permission berhasil diperbarui!');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui permission.');
    }

    public function delete($id)
    {
        $this->permissionService->deletePermission($id);
        return redirect()->route('permissions.index')->with('success', 'Permission berhasil dihapus!');
    }

    public function assign()
{
    $roleModel = new RoleModel();
    $permissionModel = new PermissionModel();
    
    $data = [
        'roles' => $roleModel->findAll(), // Ambil semua role
        'permissions' => $permissionModel->findAll(), // Ambil semua permission
    ];

    return view('permissions/assign', $data);
}

public function assignProcess()
{
    $roleId = $this->request->getPost('role_id');
    $permissions = $this->request->getPost('permissions');

    if (!$roleId || !$permissions) {
        return redirect()->back()->with('error', 'Role dan permission harus dipilih!');
    }

    $rolePermissionModel = new RolePermissionModel();

    // Hapus permission lama untuk role ini
    $rolePermissionModel->where('role_id', $roleId)->delete();

    // Simpan permission baru
    foreach ($permissions as $permissionId) {
        $rolePermissionModel->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
    }

    return redirect()->to(route_to('permissions.roles'))->with('success', 'Permissions berhasil diperbarui!');
}

public function manageRoles()
{
    $roleModel = new RoleModel();
    $permissionModel = new PermissionModel();
    $rolePermissionModel = new RolePermissionModel();

    $data['roles'] = $roleModel->findAll();
    $data['permissions'] = $permissionModel->findAll();
    
    // Ambil permission untuk setiap role
    foreach ($data['roles'] as &$role) {
        $role['permissions'] = array_column(
            $rolePermissionModel->where('role_id', $role['id'])->findAll(),
            'permission_id'
        );
    }

    return view('permissions/manage_roles', $data);
}

public function updateRolePermissions()
{
    $rolePermissionModel = new RolePermissionModel();
    $permissions = $this->request->getPost('permissions');

    if ($permissions) {
        foreach ($permissions as $roleId => $permissionIds) {
            $rolePermissionModel->assignPermissions($roleId, $permissionIds);
        }
    }

    return redirect()->to(base_url('permissions/roles'))
        ->with('success', 'Hak akses berhasil diperbarui!');
}

public function getAssignedPermissions($roleId)
{
    $rolePermissionModel = new RolePermissionModel();
    $assignedPermissions = $rolePermissionModel->where('role_id', $roleId)->findAll();

    log_message('info', '🟢 Role ID: ' . $roleId . ' - Assigned Permissions: ' . json_encode($assignedPermissions));

    return $this->response->setJSON($assignedPermissions);
}

}
