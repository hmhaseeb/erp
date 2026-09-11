<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class LogoSettings extends Component
{
    use WithFileUploads;

    public const DEFAULT_LOGO_PATH = 'logos/defaults/smallbiz-logo.png';
    public const DEFAULT_ICON_PATH = 'logos/defaults/smallbiz-icon.png';

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

    public function applyDefaultLogos()
    {
        $setting = CompanySetting::firstOrCreate(['id' => 1]);

        $setting->update([
            'main_logo' => self::DEFAULT_LOGO_PATH,
            'invoice_logo' => self::DEFAULT_LOGO_PATH,
            'report_logo' => self::DEFAULT_LOGO_PATH,
            'login_logo' => self::DEFAULT_LOGO_PATH,
            'favicon' => self::DEFAULT_ICON_PATH,
        ]);

        \App\Services\SettingsService::clearCache();

        $this->generatePwaIcons(public_path('assets/images/branding/smallbiz-icon.png'));

        session()->flash('success', 'Official SmallBiz ERP branding applied! Main Logo, Invoice PDF Logo, Report Header Logo, Login Page Logo, and Browser Favicon / PWA icons have been updated.');
        $this->mount();
        $this->dispatch('check-and-open-setup-wizard');
    }

    public function useDefault(string $type)
    {
        $setting = CompanySetting::firstOrCreate(['id' => 1]);

        if ($type === 'favicon') {
            $setting->update(['favicon' => self::DEFAULT_ICON_PATH]);
            $this->generatePwaIcons(public_path('assets/images/branding/smallbiz-icon.png'));
            session()->flash('success', 'Default SmallBiz icon applied for Browser Favicon and PWA icons.');
        } elseif (in_array($type, ['main_logo', 'invoice_logo', 'report_logo', 'login_logo'])) {
            $setting->update([$type => self::DEFAULT_LOGO_PATH]);
            session()->flash('success', 'Default SmallBiz logo applied for ' . str_replace('_', ' ', $type) . '.');
        }

        \App\Services\SettingsService::clearCache();
        $this->mount();
        $this->dispatch('check-and-open-setup-wizard');
    }

    public function removeLogo(string $type)
    {
        $setting = CompanySetting::first();
        if ($setting && in_array($type, ['main_logo', 'invoice_logo', 'report_logo', 'login_logo', 'favicon'])) {
            $setting->update([$type => null]);
            \App\Services\SettingsService::clearCache();
            session()->flash('success', ucfirst(str_replace('_', ' ', $type)) . ' cleared.');
            $this->mount();
            $this->dispatch('refresh-setup-status');
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

            // Resolve best available logo/favicon for PWA icons
            $setting->refresh();
            $favPath = $setting->favicon ?: ($setting->main_logo ?: ($setting->invoice_logo ?: $setting->login_logo));
            $sourcePath = null;

            if ($favPath) {
                $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $favPath), '/\\');
                $possible = [
                    storage_path('app/public/' . $cleanPath),
                    public_path('storage/' . $cleanPath),
                    public_path($cleanPath),
                    public_path('assets/images/branding/' . basename($cleanPath)),
                ];
                foreach ($possible as $p) {
                    if (file_exists($p) && is_file($p)) {
                        $sourcePath = $p;
                        break;
                    }
                }
            }

            if (!$sourcePath) {
                $sourcePath = public_path('assets/images/branding/smallbiz-icon.png');
            }

            $this->generatePwaIcons($sourcePath);

            session()->flash('success', 'Custom logos and PWA app icons saved successfully.');
            $this->reset(['main_logo', 'invoice_logo', 'report_logo', 'login_logo', 'favicon']);
            $this->mount();
            $this->dispatch('check-and-open-setup-wizard');
        }
    }

    public function generatePwaIcons(?string $sourceImagePath = null)
    {
        $sizes = [72, 96, 128, 144, 152, 180, 192, 384, 512];
        $dir = public_path('assets/images/icons');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!$sourceImagePath || !file_exists($sourceImagePath)) {
            $sourceImagePath = public_path('assets/images/branding/smallbiz-icon.png');
        }

        $srcImg = null;
        if (file_exists($sourceImagePath)) {
            $info = @getimagesize($sourceImagePath);
            if ($info && isset($info['mime'])) {
                $mime = strtolower($info['mime']);
                if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                    $srcImg = @imagecreatefromjpeg($sourceImagePath);
                } elseif ($mime === 'image/png') {
                    $srcImg = @imagecreatefrompng($sourceImagePath);
                } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                    $srcImg = @imagecreatefromwebp($sourceImagePath);
                } elseif ($mime === 'image/gif') {
                    $srcImg = @imagecreatefromgif($sourceImagePath);
                }
            }
        }

        if (!$srcImg) {
            return;
        }

        $srcW = imagesx($srcImg);
        $srcH = imagesy($srcImg);

        foreach ($sizes as $s) {
            $canvas = imagecreatetruecolor($s, $s);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);

            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $s, $s, $transparent);

            $ratio = min($s / $srcW, $s / $srcH);
            $dstW = max(1, (int)($srcW * $ratio));
            $dstH = max(1, (int)($srcH * $ratio));
            $dstX = (int)(($s - $dstW) / 2);
            $dstY = (int)(($s - $dstH) / 2);

            imagealphablending($canvas, true);
            imagecopyresampled($canvas, $srcImg, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);

            $fileName = ($s == 180) ? 'apple-touch-icon.png' : "icon-{$s}x{$s}.png";
            imagepng($canvas, $dir . '/' . $fileName);

            if ($s == 192 || $s == 512) {
                imagepng($canvas, $dir . "/icon-maskable-{$s}x{$s}.png");
            }

            imagedestroy($canvas);
        }

        // Also update favicon.ico
        $favCanvas = imagecreatetruecolor(32, 32);
        imagealphablending($favCanvas, false);
        imagesavealpha($favCanvas, true);
        $transparent = imagecolorallocatealpha($favCanvas, 0, 0, 0, 127);
        imagefilledrectangle($favCanvas, 0, 0, 32, 32, $transparent);
        $ratio = min(32 / $srcW, 32 / $srcH);
        $dstW = max(1, (int)($srcW * $ratio));
        $dstH = max(1, (int)($srcH * $ratio));
        $dstX = (int)((32 - $dstW) / 2);
        $dstY = (int)((32 - $dstH) / 2);
        imagealphablending($favCanvas, true);
        imagecopyresampled($favCanvas, $srcImg, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagepng($favCanvas, public_path('assets/images/favicon.ico'));
        imagepng($favCanvas, public_path('favicon.ico'));
        imagedestroy($favCanvas);

        imagedestroy($srcImg);
    }

    public function render()
    {
        return view('livewire.settings.logo-settings')
            ->layout('layouts.app', ['title' => 'Logo Management']);
    }
}
