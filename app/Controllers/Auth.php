<?php

namespace App\Controllers;

use App\Services\UserService;
use CodeIgniter\Controller;

/**
 * Controller untuk autentikasi pengguna.
 */
class Auth extends Controller
{
    /**
     * @var UserService $userService Instance dari UserService.
     */
    protected $userService;

    /**
     * Constructor untuk menginisialisasi UserService.
     */
    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Menampilkan halaman registrasi.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function register()
    {
        helper('form');
        return view('auth/register');
    }

    /**
     * Memproses data registrasi pengguna.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function processRegister()
    {
        log_message('info', '🟢 Request Method: ' . $this->request->getMethod());

        if (!$this->request->is('post')) {
            log_message('error', '🔴 Invalid Request Method: ' . $this->request->getMethod());
            return redirect()->back()->with('error', 'Invalid Request');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'name'             => 'required|min_length[3]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'whatsapp'         => 'required|regex_match[/^62[0-9]{9,13}$/]',
            'birth_date'       => 'required|valid_date[Y-m-d]',
            'gender'           => 'required|in_list[L,P]',
            'password'         => 'required|min_length[6]',
            'confirm_password' => 'matches[password]',
        ];

        if (!$this->validate($rules)) {
            log_message('error', '🔴 Validasi Gagal: ' . json_encode($validation->getErrors()));
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
            'whatsapp'   => $this->request->getPost('whatsapp'),
            'birth_date' => $this->request->getPost('birth_date'),
            'gender'     => $this->request->getPost('gender'),
            'role_id'    => $this->userService->getRoleIdBySlug('user-baru'),
            'password'   => $this->request->getPost('password')
        ];

        log_message('info', '🟢 Data diterima di Controller: ' . json_encode($data));

        $result = $this->userService->registerUser($data);

        if ($result) {
            log_message('info', '✅ Registrasi Berhasil');
            return redirect()->to('/login')->with('success', 'Registrasi berhasil! Cek email untuk aktivasi.');
        }

        log_message('error', '🔴 Registrasi Gagal');
        return redirect()->back()->with('error', 'Registrasi gagal, coba lagi.');
    }

    /**
     * Memproses aktivasi akun pengguna.
     *
     * @param string $email Email pengguna.
     * @param string $code  Kode aktivasi.
     * @return \CodeIgniter\HTTP\Response
     */
    public function activate($email, $code)
    {
        log_message('info', '🟢 Permintaan aktivasi dari: ' . $email);

        $email = urldecode($email);
        $result = $this->userService->activateUser($email, $code);

        if ($result) {
            log_message('info', '✅ Aktivasi akun berhasil: ' . $email);
            return redirect()->to('/login')->with('success', 'Akun berhasil diaktivasi, silakan login.');
        }

        log_message('error', '❌ Aktivasi akun gagal: ' . $email);
        return redirect()->to('/login')->with('error', 'Kode aktivasi salah atau akun sudah aktif.');
    }

    /**
     * Menampilkan halaman login.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function login()
    {
        return view('auth/login');
    }

    /**
     * Memproses login pengguna.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function processLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $rememberMe = $this->request->getPost('remember_me') ? true : false;

        log_message('info', '🟢 Proses login dimulai untuk: ' . $email);

        $login = $this->userService->loginUser($email, $password, $rememberMe);

        if ($login === 'not_active') {
            log_message('warning', '⚠️ Login gagal: Akun belum aktif untuk ' . $email);
            return redirect()->back()->with('error', 'Akun belum aktif.');
        }

        if (!$login) {
            log_message('error', '🔴 Login gagal untuk ' . $email);
            return redirect()->back()->with('error', 'Email atau password salah.');
        }

        // ✅ Baru aman dipanggil di sini
        $permissions = $this->userService->getPermissionsByUserId($login['id']);
        session()->set('user_permissions', $permissions);

        return redirect()->to('/')->with('success', 'Login berhasil!');
    }

    /**
     * Menampilkan halaman lupa password.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function forgotPassword()
    {
        return view('auth/forgot-password');
    }

    /**
     * Memproses permintaan lupa password.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function processForgotPassword()
    {
        $email = $this->request->getPost('email');

        if (!$this->userService->sendResetPasswordEmail($email)) {
            return redirect()->back()->with('error', 'Email tidak ditemukan atau gagal mengirim email.');
        }

        return redirect()->back()->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    /**
     * Menampilkan halaman reset password berdasarkan token.
     *
     * @param string $token Token reset password.
     * @return \CodeIgniter\HTTP\Response
     */
    public function resetPassword($token)
    {
        return view('auth/reset-password', ['token' => $token]);
    }

    /**
     * Memproses perubahan password baru.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function processResetPassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok.');
        }

        if (!$this->userService->resetPassword($token, $password)) {
            return redirect()->back()->with('error', 'Token tidak valid atau telah kadaluarsa.');
        }

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }

    /**
     * Memproses logout pengguna.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function logout()
    {
        log_message('info', '🟢 User logout');
        $this->userService->logoutUser();
        return redirect()->to('/login')->with('success', 'Logout berhasil.');
    }
}
