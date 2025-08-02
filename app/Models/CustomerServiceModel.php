<?php namespace App\Models;

use CodeIgniter\Model;

class CustomerServiceModel extends Model
{
    protected $table      = 'customer_services';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_cs', 'nama_cs'];
    protected $useTimestamps = false; // sesuaikan jika pakai created_at/updated_at
}
