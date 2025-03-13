<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['key', 'value', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getSetting($key)
    {
        return $this->where('key', $key)->first()['value'] ?? null;
    }

    /**
     * Menyimpan atau memperbarui nilai setting
     */
    public function setSetting($key, $value)
    {
        $setting = $this->where('key', $key)->first();

        if ($setting) {
            return $this->update($setting['id'], ['value' => $value]);
        } else {
            return $this->insert(['key' => $key, 'value' => $value]);
        }
    }
}
