<?php

namespace App\Models;

use CodeIgniter\Model;

class RegionModel extends Model
{
    protected $DBGroup = 'default';

    public function getProvinceName($id)
    {
        return $this->db->table('provinces')
            ->select('name')
            ->where('id', $id)
            ->get()
            ->getRow('name');
    }

    public function getCityName($id)
    {
        return $this->db->table('regencies')
            ->select('name')
            ->where('id', $id)
            ->get()
            ->getRow('name');
    }

    public function getDistrictName($id)
    {
        return $this->db->table('districts')
            ->select('name')
            ->where('id', $id)
            ->get()
            ->getRow('name');
    }

    public function getVillageName($id)
    {
        return $this->db->table('villages')
            ->select('name')
            ->where('id', $id)
            ->get()
            ->getRow('name');
    }
}
