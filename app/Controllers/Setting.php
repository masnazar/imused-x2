<?php

namespace App\Controllers;

use App\Services\SettingService;
use App\Services\MenuService;
use CodeIgniter\Controller;

/**
 * Controller untuk mengelola pengaturan aplikasi.
 */
class Setting extends BaseController
{
    protected $settingService;
    protected $menuService;

    public function __construct()
    {
        $this->settingService = new SettingService();
        $this->menuService = new MenuService();
    }

    public function index()
    {
        return redirect()->to('/settings/system');
    }

    public function systemConfig()
    {
        return view('settings/main', [
            'activeTab' => 'system',
            'system' => $this->settingService->getSystemSettings(),
        ]);
    }

    public function updateSystemConfig()
    {
        $validation = \Config\Services::validation();
        $rules = [
            'system_name' => 'required|string|max_length[255]',
            'logo'        => 'max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpg,image/jpeg]',
            'favicon'     => 'max_size[favicon,512]|is_image[favicon]|mime_in[favicon,image/png,image/x-icon,image/vnd.microsoft.icon]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->settingService->set('system_name', $this->request->getPost('system_name'));

        if ($logo = $this->request->getFile('logo')) {
            if ($logo->isValid() && !$logo->hasMoved()) {
                $logoName = $logo->getRandomName();
                $logo->move(FCPATH . 'uploads', $logoName);
                $this->settingService->set('logo', 'uploads/' . $logoName);
            }
        }

        if ($favicon = $this->request->getFile('favicon')) {
            if ($favicon->isValid() && !$favicon->hasMoved()) {
                $faviconName = $favicon->getRandomName();
                $favicon->move(FCPATH . 'uploads', $faviconName);
                $this->settingService->set('favicon', 'uploads/' . $faviconName);
            }
        }

        return redirect()->back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }

    public function emailConfig()
    {
        return view('settings/main', [
            'activeTab' => 'email',
            'email' => [
                'email_smtp_host'   => $this->settingService->get('email_smtp_host'),
                'email_smtp_user'   => $this->settingService->get('email_smtp_user'),
                'email_smtp_pass'   => $this->settingService->get('email_smtp_pass'),
                'email_smtp_port'   => $this->settingService->get('email_smtp_port'),
                'email_smtp_crypto' => $this->settingService->get('email_smtp_crypto'),
            ],
        ]);
    }

    public function updateEmailConfig()
    {
        $validation = \Config\Services::validation();
        $rules = [
            'email_smtp_host'   => 'required',
            'email_smtp_user'   => 'required|valid_email',
            'email_smtp_pass'   => 'required',
            'email_smtp_port'   => 'required|numeric',
            'email_smtp_crypto' => 'required|in_list[tls,ssl]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->settingService->set('email_smtp_host', $this->request->getPost('email_smtp_host'));
        $this->settingService->set('email_smtp_user', $this->request->getPost('email_smtp_user'));
        $this->settingService->set('email_smtp_pass', $this->request->getPost('email_smtp_pass'));
        $this->settingService->set('email_smtp_port', $this->request->getPost('email_smtp_port'));
        $this->settingService->set('email_smtp_crypto', $this->request->getPost('email_smtp_crypto'));

        return redirect()->to('/settings/email')->with('success', 'Konfigurasi email berhasil diperbarui.');
    }

    /**
     * Halaman konfigurasi menu akses.
     */
    public function menuConfig()
{
    return view('settings/main', [
        'activeTab'      => 'menu',
        'menus'          => $this->menuService->getAllMenus(), // ⬅️ Tambahkan ini!
        'roles'          => $this->menuService->getAllRoles(), // ⬅️ Tambahkan ini juga!
        'accessMap'      => $this->menuService->getAccessMap(), // ⬅️ Ini tambahan untuk centangannya
    ]);
}



    /**
     * Proses toggle akses menu.
     */
    public function toggleMenuAccess()
    {
        $menuId = $this->request->getPost('menu_id');
        $roleId = $this->request->getPost('role_id');

        $this->menuService->toggleAccess($menuId, $roleId);

        return redirect()->back()->with('success', 'Akses menu berhasil diperbarui.');
    }

    public function saveMatrixAccess()
{
    $matrix = $this->request->getPost('access');
    $this->menuService->updateFullAccessMatrix($matrix ?? []);
    return redirect()->back()->with('success', 'Akses role berhasil diperbarui.');
}

public function saveMenuRoles()
{
    $menuId = (int) $this->request->getPost('menu_id');
    $roleIds = $this->request->getPost('role_ids') ?? [];

    $this->menuService->updateRoleAccess($menuId, $roleIds);

    return redirect()->back()->with('success', 'Akses berhasil diperbarui.');
}

public function updateMenuRoles()
{
    $menuId   = (int) $this->request->getPost('menu_id');
    $roleIds  = $this->request->getPost('role_ids') ?? [];

    // Validasi input
    if (!$menuId) {
        return redirect()->back()->with('error', 'Menu tidak valid.');
    }

    $this->menuService->updateRoleAccess($menuId, $roleIds);

    return redirect()->back()->with('success', 'Akses role berhasil diperbarui.');
}


}
