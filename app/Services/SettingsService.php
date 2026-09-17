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
     * Retrieve configured currency code (defaults to 'AED')
     */
    public static function currency(): string
    {
        return self::getCompany()?->currency ?: 'AED';
    }

    /**
     * Retrieve configured currency symbol (e.g. '$', 'AED', 'Rs.')
     */
    public static function currencySymbol(): string
    {
        return self::getCompany()?->currency_symbol ?: (self::getCompany()?->currency ?: 'AED');
    }

    /**
     * Retrieve configured company country
     */
    public static function country(): string
    {
        return self::getCompany()?->country ?: 'United Arab Emirates';
    }

    /**
     * Retrieve default tax/VAT percent
     */
    public static function defaultVatPercent(): float
    {
        $company = self::getCompany();
        if ($company && $company->default_vat_percent !== null) {
            return (float) $company->default_vat_percent;
        }
        $country = strtolower(trim($company?->country ?? ''));
        $curr = strtoupper(trim($company?->currency ?? ''));
        if (str_contains($country, 'india') || $curr === 'INR') {
            return 18.00;
        }
        return 5.00;
    }

    /**
     * Retrieve tax label based on company country/currency ('GST' for India, 'VAT' for UAE/others)
     */
    public static function taxLabel(): string
    {
        $company = self::getCompany();
        $country = strtolower(trim($company?->country ?? ''));
        $curr = strtoupper(trim($company?->currency ?? ''));
        if (str_contains($country, 'india') || $curr === 'INR') {
            return 'GST';
        }
        return 'VAT';
    }

    /**
     * Retrieve tax registration number label ('GSTIN' for India, 'TRN' for UAE/others)
     */
    public static function taxNumberLabel(): string
    {
        $company = self::getCompany();
        $country = strtolower(trim($company?->country ?? ''));
        $curr = strtoupper(trim($company?->currency ?? ''));
        if (str_contains($country, 'india') || $curr === 'INR') {
            return 'GSTIN';
        }
        return 'TRN';
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
