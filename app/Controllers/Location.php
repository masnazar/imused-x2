<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProvinceModel;
use App\Models\CityModel;

class Location extends BaseController
{
    public function provinces()
    {
        return $this->response->setJSON(
            model(ProvinceModel::class)->findAll()
        );
    }

    public function cities($provinceId)
    {
        return $this->response->setJSON(
            model(CityModel::class)->where('province_id', $provinceId)->findAll()
        );
    }
}
