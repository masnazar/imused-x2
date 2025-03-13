<?php

namespace App\Services;

use App\Models\SettingModel;

class SettingService
{
    protected $settingModel;
    protected $systemSettings;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    /**
     * Get single setting value
     */
    public function get($key)
    {
        return $this->settingModel->getSetting($key);
    }

    /**
     * Set/update setting value
     */
    public function set($key, $value)
    {
        return $this->settingModel->setSetting($key, $value);
    }

    /**
     * Get all system settings with caching
     */
    public function getSystemSettings()
    {
        if (!$this->systemSettings) {
            $settings = $this->settingModel
                ->whereIn('key', ['system_name', 'logo', 'favicon'])
                ->findAll();

            $this->systemSettings = [];
            foreach ($settings as $setting) {
                $this->systemSettings[$setting['key']] = $setting['value'];
            }
        }

        return $this->systemSettings;
    }

    /**
     * Get logo URL with fallback
     */
    public function getLogo($type = 'desktop')
    {
        $defaultLogos = [
            'desktop' => 'assets/images/brand-logos/desktop-logo.png',
            'toggle-dark' => 'assets/images/brand-logos/toggle-dark.png',
            'desktop-dark' => 'assets/images/brand-logos/desktop-dark.png',
            'toggle-logo' => 'assets/images/brand-logos/toggle-logo.png',
            'toggle-white' => 'assets/images/brand-logos/toggle-white.png',
            'desktop-white' => 'assets/images/brand-logos/desktop-white.png'
        ];

        $logo = $this->getSystemSettings()['logo'] ?? null;
        return $logo ? base_url($logo) : base_url($defaultLogos[$type]);
    }

    /**
     * Get favicon URL with fallback
     */
    public function getFavicon()
    {
        $favicon = $this->getSystemSettings()['favicon'] ?? null;
        return $favicon ? base_url($favicon) : base_url('assets/images/brand-logos/favicon.ico');
    }
}