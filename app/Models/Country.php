<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        '2_letter_isocode',
        '3_letter_isocode',
        'country_name',
        'slug',
        'region',
        'language',
        'native_language_name',
        'language_code',
        'currency_code',
        'currency_name',
        'currency_to_usd_exchange_rate',
        'capital_city',
        'second_city'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'country_of_origin');
    }
} 