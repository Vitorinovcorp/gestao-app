<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price', 'currency',
        'billing_period', 'features', 'limits', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFeature(string $key, $default = null)
    {
        return data_get($this->features, $key, $default);
    }

    public function getLimit(string $key, $default = null)
    {
        return data_get($this->limits, $key, $default);
    }

    public function hasFeature(string $key): bool
    {
        return isset($this->features[$key]) && $this->features[$key] === true;
    }
}