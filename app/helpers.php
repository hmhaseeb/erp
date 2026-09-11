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
