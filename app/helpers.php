<?php

use App\Services\SettingsService;

if (!function_exists('currency')) {
    /**
     * Get the active company currency code (e.g., 'AED', 'USD', 'PKR')
     */
    function currency(): string
    {
        return SettingsService::currency();
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get the active company currency symbol (e.g., 'AED', '$', 'Rs.', '€')
     */
    function currency_symbol(): string
    {
        return SettingsService::currencySymbol();
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format an amount with currency prefix and standard decimals
     */
    function format_currency($amount, int $decimals = 2, bool $useSymbol = false): string
    {
        $prefix = $useSymbol ? currency_symbol() : currency();
        return $prefix . ' ' . number_format((float) $amount, $decimals);
    }
}

if (!function_exists('tax_name')) {
    /**
     * Get the active tax label ('GST' for India, 'VAT' for UAE/others)
     */
    function tax_name(): string
    {
        return SettingsService::taxLabel();
    }
}

if (!function_exists('tax_number_name')) {
    /**
     * Get the active tax number label ('GSTIN' for India, 'TRN' for UAE/others)
     */
    function tax_number_name(): string
    {
        return SettingsService::taxNumberLabel();
    }
}

if (!function_exists('default_tax_percent')) {
    /**
     * Get the configured default tax percentage
     */
    function default_tax_percent(): float
    {
        return SettingsService::defaultVatPercent();
    }
}

if (!function_exists('company_country')) {
    /**
     * Get the configured company country
     */
    function company_country(): string
    {
        return SettingsService::country();
    }
}
