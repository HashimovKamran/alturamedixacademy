<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteUser extends Model
{
    protected $table = 'aa_site_users';

    protected $guarded = [];

    protected $casts = [
        'email_notify' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('lang_code', $language);
    }
}