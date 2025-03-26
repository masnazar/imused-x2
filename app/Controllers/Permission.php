<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PermissionService;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\RolePermissionModel;

/**
 * Controller untuk mengelola permission dan role.
 */
class Permission extends BaseController
{
    protected $permissionService;

    /**
     * Konstruktor untuk inisialisasi service.
     */
    public function __construct()
    {
        $this->permissionService = new PermissionService();
    }

    /**
     * Menampilkan daftar permission.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();
        return view('permissions/index', compact('permissions'));
    }

    /**
     * Menampilkan form untuk membuat permission baru.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function create()
    {
        return view('permissions/create');
    }

    /**
     * Menyimpan permission baru ke database.
     *
     * @return \CodeIgniter\HTTP\Response
     */
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

    /**
     * Menampilkan form untuk mengedit permission.
     *
     * @param int $id ID permission
     * @return \CodeIgniter\HTTP\Response
     */
    public function edit($id)
    {
        $permission = $this->permissionService->getPermissionById($id);
        return view('permissions/edit', compact('permission'));
    }

    /**
     * Memperbarui data permission di database.
     *
     * @param int $id ID permission
     * @return \CodeIgniter\HTTP\Response
     */
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

    /**
     * Menghapus permission dari database.
     *
     * @param int $id ID permission
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete($id)
    {
        $this->permissionService->deletePermission($id);
        return redirect()->route('permissions.index')->with('success', 'Permission berhasil dihapus!');
    }

    /**
     * Menampilkan form untuk meng-assign permission ke role.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function assign()
    {
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        
        $data = [
            'roles' => $roleModel->findAll(),
            'permissions' => $permissionModel->findAll(),
        ];

        return view('permissions/assign', $data);
    }

    /**
     * Memproses peng-assign-an permission ke role.
     *
     * @return \CodeIgniter\HTTP\Response
     */
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

    /**
     * Menampilkan halaman untuk mengelola role dan permission.
     *
     * @return \CodeIgniter\HTTP\Response
     */
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

    /**
     * Memperbarui hak akses role dengan permission tertentu.
     *
     * @return \CodeIgniter\HTTP\Response
     */
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

    /**
     * Mendapatkan daftar permission yang telah di-assign ke role tertentu.
     *
     * @param int $roleId ID role
     * @return \CodeIgniter\HTTP\Response
     */
    public function getAssignedPermissions($roleId)
    {
        $rolePermissionModel = new RolePermissionModel();
        $assignedPermissions = $rolePermissionModel->where('role_id', $roleId)->findAll();

        log_message('info', '🟢 Role ID: ' . $roleId . ' - Assigned Permissions: ' . json_encode($assignedPermissions));

        return $this->response->setJSON($assignedPermissions);
    }
}
