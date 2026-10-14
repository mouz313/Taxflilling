<?php

use App\Models\AppSetting;
use App\Models\MenuItem;

if (!function_exists('setting')) {
    /**
     * Get or set app setting
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return app(AppSetting::class);
        }

        try {
            return AppSetting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('header_menu')) {
    /**
     * Get active header menu items
     */
    function header_menu()
    {
        try {
            return MenuItem::getHeaderMenu();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}

if (!function_exists('footer_menu')) {
    /**
     * Get active footer menu items
     */
    function footer_menu()
    {
        try {
            return MenuItem::getFooterMenu();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
