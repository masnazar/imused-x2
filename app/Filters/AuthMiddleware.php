<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Middleware untuk autentikasi pengguna.
 * Memastikan bahwa pengguna telah login dan akun mereka aktif.
 */
class AuthMiddleware implements FilterInterface
{
    /**
     * Fungsi yang dijalankan sebelum request diproses.
     * Mengecek apakah pengguna sudah login dan apakah akun mereka aktif.
     *
     * @param RequestInterface $request Objek request.
     * @param array|null $arguments Argumen tambahan (jika ada).
     * @return \CodeIgniter\HTTP\RedirectResponse|null Redirect ke halaman login jika tidak memenuhi syarat.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Log informasi tentang session saat ini
        log_message('info', '🔍 Middleware mengecek session: ' . json_encode($session->get()));

        // Cek apakah pengguna sudah login
        if (!$session->get('is_logged_in')) {
            log_message('warning', '⛔ User tidak login, redirect ke login.');
            return redirect()->to('/login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Cek apakah akun pengguna aktif
        if ($session->get('is_active') == 0) {
            log_message('warning', '⛔ User belum aktif, redirect ke login.');
            return redirect()->to('/login')->with('error', 'Akun Anda belum aktif. Silakan cek email untuk aktivasi.');
        }
    }

    /**
     * Fungsi yang dijalankan setelah request diproses.
     * Tidak ada tindakan yang diperlukan setelah request dalam middleware ini.
     *
     * @param RequestInterface $request Objek request.
     * @param ResponseInterface $response Objek response.
     * @param array|null $arguments Argumen tambahan (jika ada).
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan yang diperlukan setelah request
    }
}
