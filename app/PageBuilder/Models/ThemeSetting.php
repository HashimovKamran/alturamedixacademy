<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $table = 'page_builder_theme_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['values' => 'array'];
    }
}

