<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel customers
 */
class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name',
        'phone_number',
        'address',
        'province_id',
        'city_id',         // ini tetap untuk regency_id
        'district_id',
        'village_id',
        'postal_code',
        'dob',
        'gender',
        'order_count',
        'ltv',
        'first_order_date',
        'last_order_date',
        'average_days_between_orders',
        'days_from_last_order',
        'segment',
        'city',            // ⛔ ini sebaiknya diisi otomatis dari relasi, tapi tetap bisa dipertahankan
        'province'         // ⛔ sama kayak city, optional
    ];
}
