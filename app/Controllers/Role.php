<?php

namespace App\Controllers;

use App\Services\RoleService;
use App\Repositories\RoleRepository;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Controller untuk mengelola Role
 */
class Role extends BaseController
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * Constructor
     * Menginisialisasi RoleService dengan RoleRepository
     */
    public function __construct()
    {
        // Gunakan Dependency Injection untuk RoleService
        $this->roleService = new RoleService(new RoleRepository());
    }

    /**
     * Menampilkan daftar roles
     *
     * @return \CodeIgniter\HTTP\Response|string
     */
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return view('roles/index', compact('roles'));
    }

    /**
     * Menampilkan form untuk menambahkan role baru
     *
     * @return \CodeIgniter\HTTP\Response|string
     */
    public function create()
    {
        return view('roles/create');
    }

    /**
     * Proses menyimpan role baru ke database
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     * @throws PageNotFoundException
     */
    public function store()
    {
        log_message('info', '🟢 Memulai proses penyimpanan role');

        // Validasi metode request
        if ($this->request->getMethod() !== 'POST') {
            log_message('error', '❌ Metode request tidak valid! Request yang diterima: ' . $this->request->getMethod());
            throw PageNotFoundException::forPageNotFound();
        }

        // Ambil data dari request
        $data = $this->request->getPost();
        log_message('info', '🔍 Data yang diterima: ' . json_encode($data));

        // Simpan data role ke database
        if (!$this->roleService->createRole($data)) {
            log_message('error', '❌ Gagal menyimpan role ke database');
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan role.');
        }

        log_message('info', '✅ Role berhasil ditambahkan.');
        return redirect()->to(base_url('roles'))->with('success', 'Role berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit role berdasarkan ID
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\Response|string
     * @throws PageNotFoundException
     */
    public function edit($id)
    {
        $role = $this->roleService->getRoleById($id);
        if (!$role) {
            throw PageNotFoundException::forPageNotFound();
        }
        return view('roles/edit', compact('role'));
    }

    /**
     * Proses update role berdasarkan ID
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     * @throws PageNotFoundException
     */
    public function update($id)
    {
        // Validasi metode request
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            $this->roleService->updateRole($id, $data);
            return redirect()->to(base_url('roles'))->with('success', 'Role berhasil diperbarui!');
        }

        throw PageNotFoundException::forPageNotFound();
    }

    /**
     * Proses menghapus role berdasarkan ID
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id)
    {
        $this->roleService->deleteRole($id);
        return redirect()->to(base_url('roles'))->with('success', 'Role berhasil dihapus!');
    }
}
