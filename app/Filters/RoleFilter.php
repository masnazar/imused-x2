<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
{
    $session = session();
    $userRole = $session->get('role_name'); // Ambil nama role dari session

    log_message('info', '🔍 Role Filter - Arguments: ' . json_encode($arguments));
    log_message('info', '🔍 Role Filter - User Role: ' . $userRole);

    if (!$userRole) {
        log_message('error', '❌ Role tidak ditemukan di session!');
        return redirect()->to(base_url('dashboard'));
    }

    // Pastikan arguments (roles yang diizinkan) diubah ke lowercase
    $allowedRoles = array_map('strtolower', $arguments);
    $userRoleLower = strtolower($userRole);

    log_message('info', '🔍 Role Filter - Allowed Roles: ' . json_encode($allowedRoles));
    log_message('info', '🔍 Role Filter - User Role Lower: ' . $userRoleLower);

    if (!in_array($userRoleLower, $allowedRoles)) {
        log_message('error', '❌ Role Tidak Memiliki Akses - Role: ' . $userRole);
        return redirect()->to(base_url('dashboard'));
    }

    log_message('info', '✅ Role Memiliki Akses: ' . $userRole);
}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa setelah request
    }
}
