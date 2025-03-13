<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userPermissions = $session->get('user_permissions') ?? [];

        // ✅ Jika role admin (role_id = 1), biarkan akses semua halaman
        if ($session->get('role_id') == 1) {
            return;
        }

        // ✅ Pastikan `$userPermissions` adalah array biasa
        if (!is_array($userPermissions)) {
            log_message('error', '❌ user_permissions bukan array.');
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki izin akses.');
        }

        // ✅ Ambil permission yang dibutuhkan dari `$arguments`
        $requiredPermission = $arguments[0] ?? null;

        // 🔥 Debugging: Cek permission yang dibutuhkan
        log_message('info', '🔍 Checking permission: ' . $requiredPermission);
        log_message('info', '🔍 User permissions (Fixed Array): ' . json_encode($userPermissions));

        // ✅ Cek apakah user memiliki izin akses
        if ($requiredPermission && !in_array($requiredPermission, $userPermissions, true)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki izin untuk halaman ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu dilakukan setelah request
    }
}