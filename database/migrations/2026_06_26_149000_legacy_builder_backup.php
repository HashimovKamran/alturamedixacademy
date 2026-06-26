<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('aa_page_builder_blocks') && ! Schema::hasTable('aa_legacy_page_builder_blocks')) {
            Schema::rename('aa_page_builder_blocks', 'aa_legacy_page_builder_blocks');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('aa_legacy_page_builder_blocks') && ! Schema::hasTable('aa_page_builder_blocks')) {
            Schema::rename('aa_legacy_page_builder_blocks', 'aa_page_builder_blocks');
        }
    }
};
