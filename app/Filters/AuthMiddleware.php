<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\Services;

class AuthMiddleware implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
{
    $session = session();

    log_message('info', '🔍 Middleware mengecek session: ' . json_encode($session->get()));

    if (!$session->get('is_logged_in')) {
        log_message('warning', '⛔ User tidak login, redirect ke login.');
        return redirect()->to('/login')->with('error', 'Anda harus login terlebih dahulu.');
    }

    if ($session->get('is_active') == 0) {
        log_message('warning', '⛔ User belum aktif, redirect ke login.');
        return redirect()->to('/login')->with('error', 'Akun Anda belum aktif. Silakan cek email untuk aktivasi.');
    }
}


    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosong karena tidak ada yang perlu dilakukan setelah request
    }
}
