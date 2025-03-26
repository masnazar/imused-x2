<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Middleware untuk memeriksa apakah pengguna adalah admin.
 */
class AdminMiddleware implements FilterInterface
{
    /**
     * Fungsi yang dijalankan sebelum request diproses.
     *
     * @param RequestInterface $request Objek request.
     * @param array|null $arguments Argumen tambahan (opsional).
     * @return \CodeIgniter\HTTP\RedirectResponse|null Redirect jika kondisi tidak terpenuhi, atau null jika lolos.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Periksa apakah pengguna sudah login
        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Periksa apakah pengguna memiliki role admin (role_id = 1)
        if ($session->get('role_id') != 1) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    /**
     * Fungsi yang dijalankan setelah request diproses.
     *
     * @param RequestInterface $request Objek request.
     * @param ResponseInterface $response Objek response.
     * @param array|null $arguments Argumen tambahan (opsional).
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan yang diperlukan setelah request
    }
}
