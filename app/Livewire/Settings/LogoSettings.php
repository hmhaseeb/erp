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
                ];
                foreach ($possible as $p) {
                    if (file_exists($p) && is_file($p)) {
                        $sourcePath = $p;
                        break;
                    }
                }
            }

            $this->generatePwaIcons($sourcePath);

            session()->flash('success', 'Logos and PWA app icons updated successfully.');
            $this->mount();
        }
    }

    protected function generatePwaIcons(?string $sourceImagePath = null)
    {
        $sizes = [72, 96, 128, 144, 152, 180, 192, 384, 512];
        $dir = public_path('assets/images/icons');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $srcImg = null;
        if ($sourceImagePath && file_exists($sourceImagePath)) {
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

        imagedestroy($srcImg);
    }

    public function render()
    {
        return view('livewire.settings.logo-settings')
            ->layout('layouts.app', ['title' => 'Logo Management']);
    }
}
