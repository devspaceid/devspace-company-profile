<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'tagline',
        'description',
        'logo',
        'favicon',
        'email',
        'phone',
        'address',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'whatsapp',
    ];

    /**
     * Get the singleton company settings
     */
    public static function getSettings()
    {
        return self::first() ?? new self([
            'company_name' => 'Company Name',
            'tagline' => 'Your tagline here',
        ]);
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute()
{
    if (!$this->logo) {
        return null;
    }

    return str_starts_with($this->logo, 'http')
        ? $this->logo
        : asset('storage/' . $this->logo);
}

public function getFaviconUrlAttribute()
{
    if (!$this->favicon) {
        return null;
    }

    return str_starts_with($this->favicon, 'http')
        ? $this->favicon
        : asset('storage/' . $this->favicon);
}
}
