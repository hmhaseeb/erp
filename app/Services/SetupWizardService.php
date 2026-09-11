<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CompanySetting;
use App\Models\GeneralSetting;
use App\Models\InvoiceSetting;
use App\Models\ProductCategory;

class SetupWizardService
{
    /**
     * Get the status and metadata for all 6 setup steps in order
     */
    public static function getSteps(): array
    {
        $company = SettingsService::getCompany();
        $general = SettingsService::getGeneral();
        $invoice = SettingsService::getInvoice();
        $hasAccounts = Account::exists();
        $hasCategories = ProductCategory::exists();

        // 1. Company Settings Check
        $companyCompleted = $company !== null 
            && !empty($company->company_name) 
            && !empty($company->currency)
            && !empty($company->country);

        // 2. Logo Management Check
        $logoCompleted = $company !== null && (
            !empty($company->main_logo) || 
            !empty($company->invoice_logo) || 
            !empty($company->report_logo) || 
            !empty($company->login_logo) || 
            !empty($company->favicon)
        );

        // 3. Cash / Bank Account Check
        $accountCompleted = (bool) $hasAccounts;

        // 4. General System Settings Check
        $generalCompleted = $general !== null 
            && !empty($general->time_zone) 
            && !empty($general->date_format);

        // 5. Invoice & Numbering Settings Check
        $invoiceCompleted = $invoice !== null 
            && !empty($invoice->invoice_prefix) 
            && !empty($invoice->purchase_prefix);

        // 6. Product Categories Check
        $categoriesCompleted = (bool) $hasCategories;

        return [
            1 => [
                'id' => 'company_settings',
                'step_number' => 1,
                'title' => 'Company Profile & Currency',
                'description' => 'Configure legal business name, currency code, TRN tax number, address and phone contacts.',
                'route' => 'settings.company',
                'url' => route('settings.company'),
                'icon' => 'bx bx-buildings',
                'is_completed' => $companyCompleted,
                'status_label' => $companyCompleted ? 'Completed' : 'Pending',
                'action_label' => $companyCompleted ? 'Edit Company Profile' : 'Configure Company',
            ],
            2 => [
                'id' => 'logo_management',
                'step_number' => 2,
                'title' => 'Logo & Brand Identity',
                'description' => 'Select official SmallBiz default branding or upload header logo, invoice print logo, and favicons.',
                'route' => 'settings.logos',
                'url' => route('settings.logos'),
                'icon' => 'bx bx-image-alt',
                'is_completed' => $logoCompleted,
                'status_label' => $logoCompleted ? 'Completed' : 'Pending',
                'action_label' => $logoCompleted ? 'Manage Logos' : 'Configure Logos',
            ],
            3 => [
                'id' => 'cash_bank_account',
                'step_number' => 3,
                'title' => 'Create Cash / Bank Account',
                'description' => 'Set up initial cash in hand or bank account needed for daily payments, receipts, and system defaults.',
                'route' => 'accounts.index',
                'url' => route('accounts.index'),
                'icon' => 'bx bx-wallet',
                'is_completed' => $accountCompleted,
                'status_label' => $accountCompleted ? 'Completed' : 'Pending',
                'action_label' => $accountCompleted ? 'Manage Accounts' : 'Create Account',
            ],
            4 => [
                'id' => 'general_settings',
                'step_number' => 4,
                'title' => 'General System Settings',
                'description' => 'Set local timezone, display date formats, decimal places, and default cash/bank accounts.',
                'route' => 'settings.general',
                'url' => route('settings.general'),
                'icon' => 'bx bx-slider-alt',
                'is_completed' => $generalCompleted,
                'status_label' => $generalCompleted ? 'Completed' : 'Pending',
                'action_label' => $generalCompleted ? 'Edit System Settings' : 'Configure System',
            ],
            5 => [
                'id' => 'invoice_settings',
                'step_number' => 5,
                'title' => 'Invoice & Numbering Settings',
                'description' => 'Set invoice & purchase numbering prefixes (e.g. INV-, PUR-), starting numbers, and payment terms.',
                'route' => 'settings.invoice',
                'url' => route('settings.invoice'),
                'icon' => 'bx bx-file-blank',
                'is_completed' => $invoiceCompleted,
                'status_label' => $invoiceCompleted ? 'Completed' : 'Pending',
                'action_label' => $invoiceCompleted ? 'Edit Numbering' : 'Configure Numbering',
            ],
            6 => [
                'id' => 'product_categories',
                'step_number' => 6,
                'title' => 'Product Categories & Master Data',
                'description' => 'Add your product categories to organize items before creating inventory products.',
                'route' => 'products.categories',
                'url' => route('products.categories'),
                'icon' => 'bx bx-purchase-tag-alt',
                'is_completed' => $categoriesCompleted,
                'status_label' => $categoriesCompleted ? 'Completed' : 'Pending',
                'action_label' => $categoriesCompleted ? 'Manage Categories' : 'Add Categories',
            ],
        ];
    }

    /**
     * Get count of completed steps
     */
    public static function getCompletedCount(): int
    {
        $steps = self::getSteps();
        return count(array_filter($steps, fn($s) => $s['is_completed']));
    }

    /**
     * Get total steps count
     */
    public static function getTotalCount(): int
    {
        return 6;
    }

    /**
     * Get percentage of setup completed (0 to 100)
     */
    public static function getPercentage(): int
    {
        return (int) round((self::getCompletedCount() / self::getTotalCount()) * 100);
    }

    /**
     * Whether all setup steps are completed
     */
    public static function isComplete(): bool
    {
        return self::getCompletedCount() === self::getTotalCount();
    }

    /**
     * Get the first incomplete step or null
     */
    public static function getNextIncompleteStep(): ?array
    {
        $steps = self::getSteps();
        foreach ($steps as $step) {
            if (!$step['is_completed']) {
                return $step;
            }
        }
        return null;
    }
}
