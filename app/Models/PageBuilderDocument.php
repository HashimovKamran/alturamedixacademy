<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilderDocument extends Model
{
    protected $table = 'aa_page_builder_documents';

    protected $guarded = [];

    protected $casts = [
        'document_json' => 'array',
        'schema_version' => 'integer',
    ];
}
