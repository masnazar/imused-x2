<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Middleware untuk memastikan hanya pengguna dengan role Superuser yang bisa akses.
 */
class AdminMiddleware implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Cek login
        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Cek slug role, pastikan hanya Superuser
        if ($session->get('role_slug') !== 'superuser') {
            return redirect()->to('/dashboard')->with('error', 'Akses hanya untuk Superuser.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak dibutuhkan aksi setelah request
    }
}
