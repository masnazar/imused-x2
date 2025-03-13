<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\SettingService;
use CodeIgniter\Session\Session;
use Config\Services;

class UserService
{
    protected $userRepo;
    protected $session;
    protected $email;
    protected $settingService;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->session = session();
        $this->settingService = new SettingService();
        $this->userRepo = new UserRepository(\Config\Database::connect()); // ✅ Pastikan pakai `\Config\Database`
        helper('cookie');
    }

    public function registerUser($data)
    {
        log_message('info', '🟢 Data diterima di Service: ' . json_encode($data));

        // Hash password sebelum simpan ke database
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['activation_code'] = bin2hex(random_bytes(16));
        $data['is_active'] = 0;

        $result = $this->userRepo->createUser($data);

        if ($result) {
            log_message('info', '📩 Mengirim email aktivasi ke: ' . $data['email']);
            $this->sendActivationEmail($data['email'], $data['activation_code']);
        }

        return $result;
    }

    public function sendActivationEmail($email, $activationCode)
{
    $emailSetting = (object) [
        'smtp_host'   => $this->settingService->get('email_smtp_host'),
        'smtp_user'   => $this->settingService->get('email_smtp_user'),
        'smtp_pass'   => $this->settingService->get('email_smtp_pass'),
        'smtp_port'   => (int) $this->settingService->get('email_smtp_port'),
        'smtp_crypto' => strtolower($this->settingService->get('email_smtp_crypto')),
    ];

    $emailService = Services::email();

    $emailConfig = [
        'protocol'       => 'smtp',
        'SMTPHost'       => $emailSetting->smtp_host,
        'SMTPPort'       => $emailSetting->smtp_port,
        'SMTPUser'       => $emailSetting->smtp_user,
        'SMTPPass'       => $emailSetting->smtp_pass,
        'SMTPCrypto'     => $emailSetting->smtp_crypto,
        'mailType'       => 'html',
        'charset'        => 'utf-8',
        'CRLF'           => "\r\n",
        'newline'        => "\r\n",
        'SMTPAutoTLS'    => false,  // 🔥 WAJIB untuk Hostinger!
        'authType'       => 'LOGIN',
        'SMTPTimeout'    => 30,
        'SMTPOptions'    => [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true,
            ],
        ],
    ];

    $emailService->initialize($emailConfig);

    $activationLink = base_url("activate/" . urlencode($email) . "/" . $activationCode);
    $message = "
        <p>Hai,</p>
        <p>Terima kasih telah mendaftar. Klik link di bawah ini untuk mengaktifkan akunmu:</p>
        <p><a href='$activationLink'>$activationLink</a></p>
        <p>Jika kamu tidak merasa mendaftar, abaikan email ini.</p>
    ";

    $emailService->setTo($email);
    $emailService->setFrom($emailSetting->smtp_user, 'IMUSED-X2');
    $emailService->setSubject('Aktivasi Akun');
    $emailService->setMessage($message);

    if (!$emailService->send()) {
        log_message('error', '❌ Gagal mengirim email aktivasi: ' . json_encode($emailService->printDebugger(['headers'])));
    } else {
        log_message('info', '✅ Email aktivasi berhasil dikirim ke: ' . $email);
    }
}



    public function activateUser($email, $code)
    {
        log_message('info', '🔍 Memproses aktivasi untuk: ' . $email);

        $user = $this->userRepo->findByEmail($email);

        if (!$user || $user['activation_code'] !== $code) {
            log_message('error', '❌ Aktivasi gagal: Kode tidak valid atau akun sudah aktif.');
            return false;
        }

        log_message('info', '✅ Aktivasi berhasil untuk: ' . $email);
        return $this->userRepo->updateUser($user['id'], [
            'is_active' => 1,
            'activation_code' => null
        ]);
    }

    public function loginUser($email, $password, $rememberMe = false)
    {
        log_message('info', '🟢 Proses login untuk: ' . $email);

        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            log_message('error', '🔴 Login gagal: Email atau password salah');
            return false;
        }

        if (!$user['is_active']) {
            log_message('warning', '⚠️ Login gagal: Akun belum aktif');
            return 'not_active';
        }

        // ✅ Ambil permissions dari database
        $userPermissions = $this->getUserPermissions($user['role_id']);

        // 🔥 Fix: Pastikan `getUserPermissions()` mengembalikan array of objects
        if (!empty($userPermissions) && is_array($userPermissions) && isset($userPermissions[0]) && is_array($userPermissions[0])) {
            // Konversi dari array of arrays ke array of objects
            $userPermissions = array_map(fn($p) => (object) $p, $userPermissions);
        }

        // ✅ Konversi ke array of permission names
        $userPermissionNames = array_map(fn($p) => $p->permission_name, $userPermissions);

        // ✅ Tambahkan log ini untuk debugging
        log_message('info', '🟢 Data user sebelum set session: ' . json_encode($user));

        $this->session->set([
            'user_id'    => $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
            'role_id'    => $user['role_id'],
            'role_name'  => $user['role_name'], // 🔥 Role Name ditampilkan
            'is_active'  => $user['is_active'],
            'profile_image' => $user['profile_image'] ?? 'default.jpg', // 🔥 Tambahkan ini!
            'user_permissions' => $userPermissionNames, // 🔥 Disimpan sebagai array string
            'is_logged_in' => true,

        ]);
        

        // ✅ Tambahkan log setelah menyimpan session
        log_message('info', '🟢 Session setelah login: ' . json_encode($this->session->get()));

        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            set_cookie('remember_token', $token, 604800);
            $this->userRepo->updateUser($user['id'], ['remember_token' => $token]);
        }

        log_message('info', '✅ Login berhasil untuk: ' . $email);
        log_message('info', '🔑 Permissions stored in session: ' . json_encode($userPermissionNames));
        return true;
    }

    /**
 * 🔥 Fungsi untuk mendapatkan semua permission berdasarkan role_id
 */
public function getUserPermissions($roleId)
{
    // Jika role_id = 1 (Administrator), berikan semua permissions
    if ($roleId == 1) {
        return $this->userRepo->getAllPermissions();
    }

    return $this->userRepo->getPermissionsByRole($roleId);
}


    /**
     * Mengirim email reset password ke pengguna
     */
    public function sendResetPasswordEmail($email)
{
    $user = $this->userRepo->findByEmail($email);
    if (!$user) {
        log_message('error', '❌ Email tidak ditemukan: ' . $email);
        return false;
    }

    // Generate token reset password
    $token = bin2hex(random_bytes(32));
    log_message('info', '🟢 Token reset password: ' . $token);

    if (!$this->userRepo->saveResetToken($user['id'], $token)) {
        log_message('error', '❌ Gagal menyimpan reset token untuk user ID: ' . $user['id']);
        return false;
    }

    log_message('info', '✅ Reset token berhasil disimpan untuk user ID: ' . $user['id']);

    $resetLink = base_url("reset-password/$token");

    // **Ambil Konfigurasi SMTP dari Database**
    $emailSetting = (object) [
        'smtp_host'   => $this->settingService->get('email_smtp_host'),
        'smtp_user'   => $this->settingService->get('email_smtp_user'),
        'smtp_pass'   => $this->settingService->get('email_smtp_pass'),
        'smtp_port'   => (int) $this->settingService->get('email_smtp_port'),
        'smtp_crypto' => strtolower($this->settingService->get('email_smtp_crypto')),
    ];

    // **Inisialisasi Email Service**
    $emailService = Services::email();
    $emailConfig = [
        'protocol'       => 'smtp',
        'SMTPHost'       => $emailSetting->smtp_host,
        'SMTPPort'       => $emailSetting->smtp_port,
        'SMTPUser'       => $emailSetting->smtp_user,
        'SMTPPass'       => $emailSetting->smtp_pass,
        'SMTPCrypto'     => $emailSetting->smtp_crypto,
        'mailType'       => 'html',
        'charset'        => 'utf-8',
        'CRLF'           => "\r\n",
        'newline'        => "\r\n",
        'SMTPAutoTLS'    => false,
        'authType'       => 'LOGIN',
        'SMTPTimeout'    => 30,
        'SMTPOptions'    => [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true,
            ],
        ],
    ];

    $emailService->initialize($emailConfig);

    // **Kirim Email Reset Password**
    $message = "
        <p>Hai,</p>
        <p>Klik link di bawah ini untuk mereset password Anda:</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
    ";

    $emailService->setTo($email);
    $emailService->setFrom($emailSetting->smtp_user, 'IMUSED-X2');
    $emailService->setSubject('Reset Password');
    $emailService->setMessage($message);

    if (!$emailService->send()) {
        log_message('error', '❌ Gagal mengirim email reset password: ' . json_encode($emailService->printDebugger(['headers'])));
        return false;
    }

    log_message('info', '✅ Email reset password berhasil dikirim ke: ' . $email);
    return true;
}


    // Kode Untuk Profile User
    public function getUserById($id)
    {
        return $this->userRepo->getUserById($id);
    }

    public function updateUser($id, $data)
{
    try {
        // Debug: Log data sebelum dikirim ke repository
        log_message('info', '📤 Data yang diterima UserService: ' . json_encode($data));

        $result = $this->userRepo->updateUser($id, $data);

        if (!$result) {
            $error = $this->userRepo->getError();
            log_message('error', '❌ Gagal update UserService: ' . $error);
            return false;
        }

        return true;
    } catch (\Exception $e) {
        log_message('error', '❌ Exception di UserService: ' . $e->getMessage());
        return false;
    }
}


    /**
     * Mengatur ulang password berdasarkan token
     */
    public function resetPassword($token, $newPassword)
    {
        $user = $this->userRepo->findByResetToken($token);
        if (!$user) {
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userRepo->updatePassword($user['id'], $hashedPassword);

        return true;
    }

    public function getUserByResetToken($token)
    {
        return $this->userRepo->findByResetToken($token);
    }


    public function logoutUser()
    {
        log_message('info', '🟢 Proses logout');
        $this->session->destroy();
        delete_cookie('remember_token');
    }
}
