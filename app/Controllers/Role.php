<?php

namespace App\Controllers;

use App\Services\RoleService;
use App\Repositories\RoleRepository;
use CodeIgniter\Exceptions\PageNotFoundException;

class Role extends BaseController
{
    protected $roleService;

    public function __construct()
    {
        // ✅ Gunakan service() untuk Dependency Injection
        $this->roleService = new RoleService(new RoleRepository());
    }

    /**
     * 📌 Menampilkan daftar roles
     */
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return view('roles/index', compact('roles'));
    }

    /**
     * 📌 Menampilkan form tambah role
     */
    public function create()
    {
        return view('roles/create');
    }

    /**
     * 📌 Proses menyimpan role baru
     */
    public function store()
{
    log_message('info', '🟢 Memulai proses penyimpanan role');

    // Cek method yang diterima
    log_message('info', '🔍 Metode Request diterima: ' . $this->request->getMethod());

    // Cek isi request body
    log_message('info', '🔍 Request Body: ' . json_encode($this->request->getPost()));

    if ($this->request->getMethod() !== 'POST') {
        log_message('error', '❌ Metode request tidak valid! Request yang diterima: ' . $this->request->getMethod());
        throw PageNotFoundException::forPageNotFound();
    }

    $data = $this->request->getPost();
    log_message('info', '🔍 Data yang diterima: ' . json_encode($data));

    if (!$this->roleService->createRole($data)) {
        log_message('error', '❌ Gagal menyimpan role ke database');
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan role.');
    }

    log_message('info', '✅ Role berhasil ditambahkan.');
    return redirect()->to(base_url('roles'))->with('success', 'Role berhasil ditambahkan!');
}

    /**
     * 📌 Menampilkan form edit role berdasarkan ID
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
     * 📌 Proses update role berdasarkan ID
     */
    public function update($id)
    {
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            $this->roleService->updateRole($id, $data);
            return redirect()->to(base_url('roles'))->with('success', 'Role berhasil diperbarui!');
        }
        throw PageNotFoundException::forPageNotFound();
    }

    /**
     * 📌 Proses menghapus role berdasarkan ID
     */
    public function delete($id)
    {
        $this->roleService->deleteRole($id);
        return redirect()->to(base_url('roles'))->with('success', 'Role berhasil dihapus!');
    }
}
