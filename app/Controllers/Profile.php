<?php

namespace App\Controllers;

use App\Services\UserService;
use CodeIgniter\Controller;

class Profile extends BaseController
{
    protected $userService;
    protected $session;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->session = session();
    }

    public function edit()
{
    $userId = $this->session->get('user_id');
    $user = $this->userService->getUserById($userId);
    $userRole = $this->session->get('role_name');

    log_message('info', '🔍 Session user_data: ' . json_encode(session('user_data')));


    // Perbaikan 1: Tambahkan backslash untuk menggunakan global DateTime
    $age = null;
    if ($user->birth_date) {
        $birthDate = new \DateTime($user->birth_date); // 🔥 Tambahkan backslash
        $today = new \DateTime(); // 🔥 Tambahkan backslash
        $age = $today->diff($birthDate)->y;
    }

    return view('profile/edit', [
        'user' => $user,
        'userRole' => $userRole,
        'age' => $age
    ]);
}

public function update()
{
    $userId = $this->session->get('user_id');

    // Validasi form
    $validationRules = [
        'name'  => 'required|min_length[3]',
        'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
        'whatsapp_number' => 'permit_empty|numeric|max_length[20]',
        'bio' => 'permit_empty|max_length[500]',
        'profile_picture' => 'max_size[profile_picture,2048]|is_image[profile_picture]'
    ];

    if (!$this->validate($validationRules)) {
        log_message('error', '❌ Validasi gagal: ' . json_encode($this->validator->getErrors()));
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    // Debugging: Cek data yang diterima dari form
    log_message('info', '🔄 Data POST yang diterima: ' . json_encode($this->request->getPost()));

    try {
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'whatsapp' => $this->request->getPost('whatsapp_number'), // Sesuaikan dengan nama kolom di database
            'bio' => $this->request->getPost('bio')
        ];

        // Handle upload gambar profil
        $profilePicture = $this->request->getFile('profile_picture');
        if ($profilePicture->isValid() && !$profilePicture->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/profile_pictures/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $profilePicture->getRandomName();
            $profilePicture->move($uploadPath, $newName);
            $data['profile_image'] = 'profile_pictures/' . $newName;

            log_message('info', '📁 Path gambar disimpan: ' . $data['profile_image']);
        }

        // Debugging: Pastikan data sebelum dikirim ke UserService
        log_message('info', '📤 Data yang akan dikirim ke UserService: ' . print_r($data, true));

        // Kirim data ke UserService untuk update
        if ($this->userService->updateUser($userId, $data)) {
            // Perbarui session jika berhasil
            $this->session->set('user_name', $data['name']);
            if (isset($data['profile_image'])) {
                $this->session->set('profile_picture', $data['profile_image']);
            }

            log_message('info', '✅ Update berhasil untuk user ID: ' . $userId);
            return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
        }

    } catch (\Exception $e) {
        log_message('error', '❌ Exception: ' . $e->getMessage());
        log_message('error', '🔥 Exception Trace: ' . $e->getTraceAsString());
    }

    log_message('error', '❌ Update gagal untuk user ID: ' . $userId);
    return redirect()->back()->with('error', 'Gagal memperbarui profil.');
}

public function updatePassword()
{
    $userId = session('user_id');

    // Validasi input
    $validationRules = [
        'current_password'  => 'required',
        'new_password'      => 'required|min_length[6]',
        'confirm_password'  => 'matches[new_password]'
    ];

    if (!$this->validate($validationRules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $user = $this->userService->getUserById($userId);

    if (!password_verify($this->request->getPost('current_password'), $user->password)) {
        return redirect()->back()->with('error', 'Password lama salah.');
    }

    // Update password baru
    $hashedPassword = password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT);
    $this->userService->updateUser($userId, ['password' => $hashedPassword]);

    return redirect()->back()->with('success', 'Password berhasil diperbarui.');
}

}
