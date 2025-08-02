<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\SettingService;
use CodeIgniter\Session\Session;
use Config\Services;

/**
 * Class UserService
 * @package App\Services
 * Handles user-related operations such as registration, login, and profile management.
 */
class UserService
{
    protected $userRepo;
    protected $session;
    protected $settingService;

    /**
     * UserService constructor.
     * Initializes dependencies and helpers.
     */
    public function __construct()
    {
        $this->userRepo = new UserRepository(\Config\Database::connect());
        $this->session = session();
        $this->settingService = new SettingService();
        helper('cookie');
    }

    /**
     * Retrieves SMTP configuration for email services.
     *
     * @return array
     */
    private function getEmailConfig(): array
    {
        $emailSetting = (object) [
            'smtp_host'   => $this->settingService->get('email_smtp_host'),
            'smtp_user'   => $this->settingService->get('email_smtp_user'),
            'smtp_pass'   => $this->settingService->get('email_smtp_pass'),
            'smtp_port'   => (int) $this->settingService->get('email_smtp_port'),
            'smtp_crypto' => strtolower($this->settingService->get('email_smtp_crypto')),
        ];

        return [
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
    }

    /**
     * Registers a new user.
     *
     * @param array $data User data.
     * @return bool
     */
    public function registerUser($data)
    {
        log_message('info', '🟢 Data diterima di Service: ' . json_encode($data));

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

    /**
     * Sends an activation email to the user.
     *
     * @param string $email User email.
     * @param string $activationCode Activation code.
     */
    public function sendActivationEmail($email, $activationCode)
    {
        $emailService = Services::email();
        $config = $this->getEmailConfig();
        $emailService->initialize($config);

        $activationLink = base_url("activate/" . urlencode($email) . "/" . $activationCode);
        $message = "
            <p>Hai,</p>
            <p>Terima kasih telah mendaftar. Klik link di bawah ini untuk mengaktifkan akunmu:</p>
            <p><a href='$activationLink'>$activationLink</a></p>
            <p>Jika kamu tidak merasa mendaftar, abaikan email ini.</p>
        ";

        $emailService->setTo($email);
        $emailService->setFrom($config['SMTPUser'], 'IMUSED-X2');
        $emailService->setSubject('Aktivasi Akun');
        $emailService->setMessage($message);

        if (!$emailService->send()) {
            log_message('error', '❌ Gagal mengirim email aktivasi: ' . json_encode($emailService->printDebugger(['headers'])));
        } else {
            log_message('info', '✅ Email aktivasi berhasil dikirim ke: ' . $email);
        }
    }

    /**
     * Activates a user account.
     *
     * @param string $email User email.
     * @param string $code Activation code.
     * @return bool
     */
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

    /**
     * Logs in a user.
     *
     * @param string $email User email.
     * @param string $password User password.
     * @param bool $rememberMe Remember me option.
     * @return bool|string
     */
    public function loginUser($email, $password, $rememberMe = false)
    {
        log_message('info', '🟢 Proses login untuk: ' . $email);

        $user = $this->userRepo->getUserWithRoleSlugByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            log_message('error', '🔴 Login gagal: Email atau password salah');
            return false;
        }

        if (!$user['is_active']) {
            log_message('warning', '⚠️ Login gagal: Akun belum aktif');
            return 'not_active';
        }

        $userPermissions = $this->getUserPermissions($user['role_id']);

        if (!empty($userPermissions) && is_array($userPermissions) && isset($userPermissions[0]) && is_array($userPermissions[0])) {
            $userPermissions = array_map(fn($p) => (object) $p, $userPermissions);
        }

        $userPermissionNames = array_map(fn($p) => $p->permission_name, $userPermissions);

        $this->session->set([
            'user_id'    => $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
            'role_id'    => $user['role_id'],
            'role_name'  => $user['role_name'],
            'role_slug'  => $user['role_slug'],
            'is_active'  => $user['is_active'],
            'profile_image' => $user['profile_image'] ?? 'default.jpg',
            'user_permissions' => $userPermissionNames,
            'is_logged_in' => true,
        ]);

        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            set_cookie('remember_token', $token, 604800);
            $this->userRepo->updateUser($user['id'], ['remember_token' => $token]);
        }

        log_message('info', '✅ Login berhasil untuk: ' . $email);
        return $user;
    }

    /**
     * Retrieves user permissions based on role ID.
     *
     * @param int $roleId Role ID.
     * @return array
     */
    public function getUserPermissions($roleId)
    {
        if ($roleId == 1) {
            return $this->userRepo->getAllPermissions();
        }

        return $this->userRepo->getPermissionsByRole($roleId);
    }

    /**
     * Sends a reset password email to the user.
     *
     * @param string $email User email.
     * @return bool
     */
    public function sendResetPasswordEmail($email)
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            log_message('error', '❌ Email tidak ditemukan: ' . $email);
            return false;
        }

        $token = bin2hex(random_bytes(32));
        log_message('info', '🟢 Token reset password: ' . $token);

        if (!$this->userRepo->saveResetToken($user['id'], $token)) {
            log_message('error', '❌ Gagal menyimpan reset token untuk user ID: ' . $user['id']);
            return false;
        }

        $resetLink = base_url("reset-password/$token");

        $emailService = Services::email();
        $config = $this->getEmailConfig();
        $emailService->initialize($config);

        $message = "
            <p>Hai,</p>
            <p>Klik link di bawah ini untuk mereset password Anda:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
        ";

        $emailService->setTo($email);
        $emailService->setFrom($config['SMTPUser'], 'IMUSED-X2');
        $emailService->setSubject('Reset Password');
        $emailService->setMessage($message);

        if (!$emailService->send()) {
            log_message('error', '❌ Gagal mengirim email reset password: ' . json_encode($emailService->printDebugger(['headers'])));
            return false;
        }

        log_message('info', '✅ Email reset password berhasil dikirim ke: ' . $email);
        return true;
    }

    /**
     * Retrieves a user by their ID.
     *
     * @param int $id User ID.
     * @return array|null
     */
    public function getUserById($id)
    {
        return $this->userRepo->getUserById($id);
    }

    /**
     * Updates user data.
     *
     * @param int $id User ID.
     * @param array $data User data.
     * @return bool
     */
    public function updateUser($id, $data)
    {
        try {
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
     * Resets a user's password using a token.
     *
     * @param string $token Reset token.
     * @param string $newPassword New password.
     * @return bool
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

    /**
     * Retrieves a user by their reset token.
     *
     * @param string $token Reset token.
     * @return array|null
     */
    public function getUserByResetToken($token)
    {
        return $this->userRepo->findByResetToken($token);
    }

    /**
     * Logs out the current user.
     */
    public function logoutUser()
    {
        log_message('info', '🟢 Proses logout');
        $this->session->destroy();
        delete_cookie('remember_token');
    }

    public function getPermissionsByUserId(int $userId): array
{
    $repo = new \App\Repositories\UserRepository();
    $permissions = $repo->getUserPermissions($userId);
    return array_column($permissions, 'permission_name');
}

public function getRoleIdBySlug(string $slug): ?int
{
    $role = model('RoleModel')->where('slug', $slug)->first();
    return $role['id'] ?? null;
}


}
