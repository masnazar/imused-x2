<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProvinceModel;
use App\Models\RegencyModel;
use App\Models\DistrictModel;
use App\Models\VillageModel;
use CodeIgniter\API\ResponseTrait;

/**
 * 📦 API Wilayah Lokal
 * 🔁 Mengambil data Provinsi, Kabupaten/Kota, Kecamatan, dan Desa dari DB
 */
class Region extends BaseController
{
    use ResponseTrait;

    public function provinces()
    {
        $model = new ProvinceModel();
        return $this->respond(
            $model->select('id, name')->orderBy('name')->findAll()
        );
    }

   public function cities($provinceId = null)
{
    if (!$provinceId || !is_numeric($provinceId)) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Invalid province ID']);
    }

        $model = new RegencyModel();
        return $this->respond(
            $model->where('province_id', $provinceId)->select('id, name')->orderBy('name')->findAll()
        );
    }

    public function districts($regencyId = null)
    {
        if (!$regencyId) return $this->failValidationError('ID kabupaten/kota tidak boleh kosong.');

        $model = new DistrictModel();
        return $this->respond(
            $model->where('regency_id', $regencyId)->select('id, name')->orderBy('name')->findAll()
        );
    }

    public function villages($districtId = null)
    {
        if (!$districtId) return $this->failValidationError('ID kecamatan tidak boleh kosong.');

        $model = new VillageModel();
        return $this->respond(
            $model->where('district_id', $districtId)->select('id, name')->orderBy('name')->findAll()
        );
    }

    public function postalCode($villageId)
{
    if (!$villageId) return $this->failValidationError('ID desa tidak valid.');

    $model = new \App\Models\VillageModel();
    $village = $model->select('postal_code')->find($villageId);

    if (!$village) return $this->failNotFound('Data desa tidak ditemukan.');

    return $this->respond($village);
}

}
