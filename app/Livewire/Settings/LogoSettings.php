<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class LogoSettings extends Component
{
    use WithFileUploads;

    public $main_logo, $invoice_logo, $report_logo, $login_logo, $favicon;
    public $existing_main_logo, $existing_invoice_logo, $existing_report_logo, $existing_login_logo, $existing_favicon;

    public function mount()
    {
        $setting = CompanySetting::first();
        if ($setting) {
            $this->existing_main_logo = $setting->main_logo;
            $this->existing_invoice_logo = $setting->invoice_logo;
            $this->existing_report_logo = $setting->report_logo;
            $this->existing_login_logo = $setting->login_logo;
            $this->existing_favicon = $setting->favicon;
        }
    }

    public function saveLogos()
    {
        $this->validate([
            'main_logo' => 'nullable|image|max:2048',
            'invoice_logo' => 'nullable|image|max:2048',
            'report_logo' => 'nullable|image|max:2048',
            'login_logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
        ]);

        $setting = CompanySetting::firstOrCreate(['id' => 1]);

        $data = [];
        if ($this->main_logo) {
            $data['main_logo'] = $this->main_logo->store('logos', 'public');
        }
        if ($this->invoice_logo) {
            $data['invoice_logo'] = $this->invoice_logo->store('logos', 'public');
        }
        if ($this->report_logo) {
            $data['report_logo'] = $this->report_logo->store('logos', 'public');
        }
        if ($this->login_logo) {
            $data['login_logo'] = $this->login_logo->store('logos', 'public');
        }
        if ($this->favicon) {
            $data['favicon'] = $this->favicon->store('logos', 'public');
        }

        if (count($data) > 0) {
            $setting->update($data);
            \App\Services\SettingsService::clearCache();
            session()->flash('success', 'Logos updated successfully.');
            $this->mount();
        }
    }

    public function render()
    {
        return view('livewire.settings.logo-settings')
            ->layout('layouts.app', ['title' => 'Logo Management']);
    }
}
