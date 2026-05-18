<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $table = 'company_settings';
    
    protected $fillable = [
        'logo_path',
        'name',
        'address',
        'postal_code',
        'city',
        'tax_number',
        'email',
        'phone',
        'website'
    ];

    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'name' => 'Minha Empresa'
            ]);
        }
        
        return $settings;
    }
}