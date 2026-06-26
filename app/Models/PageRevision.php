<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageRevision extends Model
{
    protected $table = 'aa_page_revisions';

    protected $guarded = [];

    protected $casts = [
        'blocks_json' => 'array',
        'document_json' => 'array',
        'document_schema_version' => 'integer',
    ];
}
