<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\SoscomTeamService;

class SoscomTeam extends BaseController
{
    protected SoscomTeamService $service;

    public function __construct()
    {
        $this->service = new SoscomTeamService();
    }

    public function index()
    {
        $teams = $this->service->getAll();
        return view('soscom_teams/index', compact('teams'));
    }

    public function create()
    {
        return view('soscom_teams/create');
    }

    public function store()
    {
        $data = $this->request->getPost();
        $this->service->createTeam($data);
        return redirect()->to('/soscom-teams')->with('success', 'Team berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $team = $this->service->getById((int) $id);
        return view('soscom_teams/edit', compact('team'));
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $this->service->updateTeam((int)$id, $data);
        return redirect()->to('/soscom-teams')->with('success', 'Team berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->service->deleteTeam((int)$id);
        return redirect()->to('/soscom-teams')->with('success', 'Team berhasil dihapus.');
    }
}
