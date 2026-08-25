<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\GeneralSetting;
use App\Models\InvoiceSetting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const CACHE_KEY_COMPANY = 'app_company_settings';
    public const CACHE_KEY_GENERAL = 'app_general_settings';
    public const CACHE_KEY_INVOICE = 'app_invoice_settings';

    /**
     * Retrieve cached Company Setting
     */
    public static function getCompany(): ?CompanySetting
    {
        return Cache::rememberForever(self::CACHE_KEY_COMPANY, function () {
            return CompanySetting::first();
        });
    }

    /**
     * Retrieve cached General Setting
     */
    public static function getGeneral(): ?GeneralSetting
    {
        return Cache::rememberForever(self::CACHE_KEY_GENERAL, function () {
            return GeneralSetting::first();
        });
    }

    /**
     * Retrieve cached Invoice Setting
     */
    public static function getInvoice(): ?InvoiceSetting
    {
        return Cache::rememberForever(self::CACHE_KEY_INVOICE, function () {
            return InvoiceSetting::first();
        });
    }

    /**
     * Invalidate all settings caches immediately
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_COMPANY);
        Cache::forget(self::CACHE_KEY_GENERAL);
        Cache::forget(self::CACHE_KEY_INVOICE);
    }
}
