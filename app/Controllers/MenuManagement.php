<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\RoleModel;
use App\Services\MenuService;
use CodeIgniter\HTTP\RedirectResponse;

class MenuManagement extends BaseController
{
    protected $menuModel;
    protected $roleModel;
    protected $menuService;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->roleModel = new RoleModel();
        $this->menuService = new MenuService();
    }

    public function index()
    {
        $menuId = $this->request->getGet('id');

        $menu = null;
        if ($menuId) {
            $menu = $this->menuModel->find($menuId);
        }

        $menus = $this->menuModel
            ->orderBy('parent_id', 'asc')
            ->orderBy('sort_order', 'asc')
            ->findAll();

        return view('settings/menus/index', [
            'menus' => $menus,
            'menu'  => $menu,
            'roles' => $this->roleModel->findAll(),
        ]);
    }

    public function save(): RedirectResponse
{
    $data = $this->request->getPost();

    $slug = url_title($data['name'], '-', true);
    $route = trim($data['route']) ?: null;
    $isSection = $this->request->getPost('is_section') ? 1 : 0;

    $saveData = [
            'name'       => $data['name'],
            'slug'       => $slug,
            'route'      => $isSection ? null : $route,
            'icon'       => $isSection ? null : $data['icon'],
            'parent_id'  => $isSection ? null : ($data['parent_id'] ?: null),
            'is_section' => $isSection,
            'sort_order' => (int) $data['sort_order'],
            'is_active'  => (int) $data['is_active'],
        ];


    $db = \Config\Database::connect();
    $db->transStart();

    if (!empty($data['id'])) {
        $this->menuModel->update($data['id'], $saveData);
        $menuId = (int) $data['id'];
    } else {
        $menuId = $this->menuModel->insert($saveData, true);
    }

    // ✅ Hanya update roles jika dikirim
    if ($this->request->getPost('roles') !== null) {
        $roleIds = (array) $this->request->getPost('roles');
        $this->menuService->updateRoleAccess($menuId, $roleIds);
    }

    $db->transComplete();

    return redirect()->to('/settings/menus')->with('success', 'Menu berhasil disimpan.');
}


    public function delete($id): RedirectResponse
    {
        $this->menuModel->delete($id);
        return redirect()->to('/settings/menus')->with('success', 'Menu berhasil dihapus.');
    }
}
