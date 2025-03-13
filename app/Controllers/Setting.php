<?php

namespace App\Controllers;

use App\Services\SettingService;
use CodeIgniter\Controller;

class Setting extends BaseController
{
    protected $settingService;

    public function __construct()
    {
        $this->settingService = new SettingService();
    }

    public function index()
    {
        return redirect()->to('/settings/system');
    }

    public function emailConfig()
    {
        $data = [
            'activeTab' => 'email',
            'email' => [
                'email_smtp_host' => $this->settingService->get('email_smtp_host'),
                'email_smtp_user' => $this->settingService->get('email_smtp_user'),
                'email_smtp_pass' => $this->settingService->get('email_smtp_pass'),
                'email_smtp_port' => $this->settingService->get('email_smtp_port'),
                'email_smtp_crypto' => $this->settingService->get('email_smtp_crypto')
            ]
        ];
        return view('settings/main', $data);
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
        session()->setFlashdata('error', 'Validasi gagal! Periksa kembali inputan.');
        return redirect()->back()->withInput()->with('errors', $validation->getErrors());
    }

    $this->settingService->set('email_smtp_host', $this->request->getPost('email_smtp_host'));
    $this->settingService->set('email_smtp_user', $this->request->getPost('email_smtp_user'));
    $this->settingService->set('email_smtp_pass', $this->request->getPost('email_smtp_pass'));
    $this->settingService->set('email_smtp_port', $this->request->getPost('email_smtp_port'));
    $this->settingService->set('email_smtp_crypto', $this->request->getPost('email_smtp_crypto'));

    session()->setFlashdata('success', 'Konfigurasi email berhasil diperbarui.');
    log_message('info', 'Flash Message set: ' . json_encode(session()->getFlashdata()));

    return redirect()->to('/settings/email');
}

    public function systemConfig()
    {
        $data = [
            'activeTab' => 'system',
            'system' => $this->settingService->getSystemSettings()
        ];
        return view('settings/main', $data);
    }

    public function updateSystemConfig()
    {
        $validation = \Config\Services::validation();
        $rules = [
            'system_name' => 'required|string|max_length[255]',
            'logo'        => 'max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpg,image/jpeg]', // ❌ Hapus uploaded[logo]
            'favicon'     => 'max_size[favicon,512]|is_image[favicon]|mime_in[favicon,image/png,image/x-icon,image/vnd.microsoft.icon]', // ❌ Hapus uploaded[favicon]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Simpan system_name (selalu diupdate)
        $this->settingService->set('system_name', $this->request->getPost('system_name'));

        // Upload logo HANYA jika ada file baru
        $logo = $this->request->getFile('logo');
        if ($logo !== null && $logo->isValid() && !$logo->hasMoved()) {
            $newName = $logo->getRandomName();
            $logo->move(FCPATH . 'uploads', $newName);
            $this->settingService->set('logo', 'uploads/' . $newName);
        }

        // Upload favicon HANYA jika ada file baru
        $favicon = $this->request->getFile('favicon');
        if ($favicon !== null && $favicon->isValid() && !$favicon->hasMoved()) {
            $newName = $favicon->getRandomName();
            $favicon->move(FCPATH . 'uploads', $newName);
            $this->settingService->set('favicon', 'uploads/' . $newName);
        }

        return redirect()->back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }

}
