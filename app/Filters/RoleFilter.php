<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * RoleFilter
 * 
 * Filter ini digunakan untuk memeriksa apakah pengguna memiliki role yang sesuai
 * sebelum mengakses suatu halaman atau fitur tertentu.
 */
class RoleFilter implements FilterInterface
{
    /**
     * Fungsi yang dijalankan sebelum request diproses.
     * 
     * @param RequestInterface $request Objek request yang sedang diproses.
     * @param array|null $arguments Daftar role yang diizinkan untuk mengakses.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|null Redirect ke halaman dashboard jika akses ditolak.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = $session->get('role_name'); // Ambil nama role dari session

        // Logging informasi awal
        log_message('info', '🔍 Role Filter - Arguments: ' . json_encode($arguments));
        log_message('info', '🔍 Role Filter - User Role: ' . $userRole);

        // Jika role tidak ditemukan di session, redirect ke dashboard
        if (!$userRole) {
            log_message('error', '❌ Role tidak ditemukan di session!');
            return redirect()->to(base_url('dashboard'));
        }

        // Ubah daftar role yang diizinkan ke lowercase untuk perbandingan
        $allowedRoles = array_map('strtolower', $arguments);
        $userRoleLower = strtolower($userRole);

        // Logging informasi role yang diizinkan dan role pengguna
        log_message('info', '🔍 Role Filter - Allowed Roles: ' . json_encode($allowedRoles));
        log_message('info', '🔍 Role Filter - User Role Lower: ' . $userRoleLower);

        // Jika role pengguna tidak ada dalam daftar role yang diizinkan, redirect ke dashboard
        if (!in_array($userRoleLower, $allowedRoles)) {
            log_message('error', '❌ Role Tidak Memiliki Akses - Role: ' . $userRole);
            return redirect()->to(base_url('dashboard'));
        }

        // Logging jika role memiliki akses
        log_message('info', '✅ Role Memiliki Akses: ' . $userRole);
    }

    /**
     * Fungsi yang dijalankan setelah request diproses.
     * 
     * @param RequestInterface $request Objek request yang sedang diproses.
     * @param ResponseInterface $response Objek response yang dihasilkan.
     * @param array|null $arguments Daftar role yang diizinkan untuk mengakses.
     * 
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa setelah request
    }
}
