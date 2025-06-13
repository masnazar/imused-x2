<?php

namespace App\Services;

use App\Repositories\BrandRepository;
use CodeIgniter\Validation\ValidationInterface;
use App\Models\SupplierModel;

class BrandService
{
    protected $brandRepo;
    protected $validation;

    public function __construct(BrandRepository $brandRepo, ValidationInterface $validation)
    {
        $this->brandRepo = $brandRepo;
        $this->validation = $validation;
        $this->supplierModel = new SupplierModel(); // ✅ Load SupplierModel
    }

    /**
     * 📌 Ambil semua brand
     */
    public function getAllBrands()
    {
        return $this->brandRepo->getAllBrands();
    }

    /**
     * 📌 Ambil brand berdasarkan ID
     */
    public function getBrandById($id)
    {
        return $this->brandRepo->getBrandById($id);
    }

    /**
     * 📌 Validasi dan simpan brand baru
     */
    public function createBrand($data)
    {
        $rules = [
            'supplier_id'    => 'required|integer',
            'kode_brand'     => 'required|alpha_numeric_space|min_length[3]|max_length[20]',
            'brand_name'     => 'required|string|max_length[255]',
            'primary_color'  => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'secondary_color'=> 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'accent_color'   => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'created_at'     => 'permit_empty|valid_date',
            'updated_at'     => 'permit_empty|valid_date',
            'deleted_at'     => 'permit_empty|valid_date',
        ];

        if (!$this->validation->setRules($rules)->run($data)) {
            return ['error' => $this->validation->getErrors()];
        }

        return $this->brandRepo->insertBrand($data);
    }

    /**
     * 📌 Validasi & update brand
     */
    public function updateBrand($id, $data)
    {
        return $this->brandRepo->updateBrand($id, $data);
    }

    /**
     * 📌 Hapus brand berdasarkan ID
     */
    public function deleteBrand($id)
    {
        return $this->brandRepo->deleteBrand($id);
    }

    public function getAllSuppliers()
{
    $supplierModel = new SupplierModel();
    return $supplierModel->findAll();
}

}
