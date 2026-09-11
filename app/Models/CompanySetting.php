<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getInvoiceLogoSrcAttribute()
    {
        $logoPath = $this->invoice_logo ?: $this->main_logo;

        if (!$logoPath) {
            $defaultLogo = public_path('assets/images/branding/smallbiz-logo.png');
            if (file_exists($defaultLogo) && is_file($defaultLogo)) {
                $mime = mime_content_type($defaultLogo) ?: 'image/png';
                $base64 = base64_encode(file_get_contents($defaultLogo));
                return "data:{$mime};base64,{$base64}";
            }
            return null;
        }

        $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $logoPath), '/\\');

        $possiblePaths = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
            public_path('assets/images/branding/' . basename($cleanPath)),
        ];

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath) && is_file($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $base64 = base64_encode(file_get_contents($fullPath));
                return "data:{$mime};base64,{$base64}";
            }
        }

        return asset('storage/' . $cleanPath);
    }
}
