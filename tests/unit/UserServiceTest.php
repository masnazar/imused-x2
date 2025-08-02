<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use App\Services\UserService;

final class UserServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Services::reset();
    }

    public function testRegisterUserHashesPasswordAndSendsEmail(): void
    {
        $repo = new class {
            public array $data;
            public function createUser($data)
            {
                $this->data = $data;
                return true;
            }
        };

        $service = new class($repo) extends UserService {
            public bool $emailSent = false;
            public function __construct($repo)
            {
                $this->userRepo = $repo;
                $this->session = Services::session();
            }
            public function sendActivationEmail($email, $activationCode)
            {
                $this->emailSent = true;
            }
        };

        $data = [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'secret',
        ];

        $result = $service->registerUser($data);

        $this->assertTrue($result);
        $this->assertTrue(password_verify('secret', $repo->data['password']));
        $this->assertSame(0, $repo->data['is_active']);
        $this->assertArrayHasKey('activation_code', $repo->data);
        $this->assertTrue($service->emailSent);
    }

    public function testLoginUserSetsSessionAndReturnsUser(): void
    {
        $hashed = password_hash('secret', PASSWORD_BCRYPT);

        $repo = new class($hashed) {
            private string $password;
            public function __construct($password)
            {
                $this->password = $password;
            }
            public function getUserWithRoleSlugByEmail($email)
            {
                return [
                    'id' => 1,
                    'name' => 'Tester',
                    'email' => $email,
                    'password' => $this->password,
                    'role_id' => 1,
                    'role_name' => 'Admin',
                    'role_slug' => 'admin',
                    'is_active' => 1,
                ];
            }
            public function getUserPermissions($roleId)
            {
                return [['permission_name' => 'manage_all']];
            }
            public function getAllPermissions()
            {
                return [['permission_name' => 'manage_all']];
            }
            public function updateUser($id, $data)
            {
                // no-op
            }
        };

        $service = new class($repo) extends UserService {
            public function __construct($repo)
            {
                $this->userRepo = $repo;
                $this->session = Services::session();
            }
            public function sendActivationEmail($email, $activationCode)
            {
                // noop
            }
        };

        $result = $service->loginUser('tester@example.com', 'secret');

        $this->assertIsArray($result);
        $this->assertSame(1, session()->get('user_id'));
        $this->assertTrue(session()->get('is_logged_in'));
    }
}

