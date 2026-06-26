<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const TABLES = [
        'aa_page_builder_documents' => 'aa_legacy_page_builder_documents',
        'aa_page_builder_blocks' => 'aa_legacy_page_builder_blocks',
        'aa_page_publications' => 'aa_legacy_page_publications',
        'aa_page_revisions' => 'aa_legacy_page_revisions',
        'aa_block_patterns' => 'aa_legacy_block_patterns',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES, true) as $from => $to) {
            if (Schema::hasTable($to) && ! Schema::hasTable($from)) {
                Schema::rename($to, $from);
            }
        }
    }
};
