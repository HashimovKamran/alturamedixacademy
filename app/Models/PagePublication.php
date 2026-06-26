<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePublication extends Model
{
    protected $table = 'aa_page_publications';

    protected $guarded = [];

    protected $casts = [
        'blocks_json' => 'array',
        'document_json' => 'array',
        'document_schema_version' => 'integer',
        'published_at' => 'datetime',
    ];
}
