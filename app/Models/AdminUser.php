<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    protected $table = 'aa_admin_users';

    protected $guarded = [];

    protected $casts = [
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

    public function hasAnyRole(array $roles): bool
    {
        $role = trim((string) $this->role) ?: 'super_admin';
        return $role === 'super_admin' || in_array($role, $roles, true);
    }
}
