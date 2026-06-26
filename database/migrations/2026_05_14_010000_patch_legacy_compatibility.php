<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aa_menus') && ! Schema::hasColumn('aa_menus', 'parent_id')) {
            Schema::table('aa_menus', function (Blueprint $table): void {
                $table->unsignedBigInteger('parent_id')->nullable()->after('lang_code')->index();
            });
        }

        if (Schema::hasTable('aa_site_users') && ! Schema::hasColumn('aa_site_users', 'avatar_url')) {
            Schema::table('aa_site_users', function (Blueprint $table): void {
                $table->string('avatar_url', 700)->nullable()->after('google_id');
            });
        }

        if (! Schema::hasTable('aa_visual_edits')) {
            Schema::create('aa_visual_edits', function (Blueprint $table): void {
                $table->id();
                $table->string('lang_code', 10)->default('az')->index();
                $table->string('page_key', 120)->index();
                $table->string('selector', 360);
                $table->string('edit_type', 40)->index();
                $table->longText('edit_value')->nullable();
                $table->longText('extra_json')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->unique(['lang_code', 'page_key', 'selector', 'edit_type'], 'aa_visual_edits_unique_selector_type');
            });
        }

        if (! Schema::hasTable('aa_visual_blocks')) {
            Schema::create('aa_visual_blocks', function (Blueprint $table): void {
                $table->id();
                $table->string('lang_code', 10)->default('az')->index();
                $table->string('page_key', 120)->index();
                $table->string('target_selector', 360)->default('main');
                $table->longText('block_html');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->index(['lang_code', 'page_key', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        // Uyğunluq migration-u geriyə silmir; mövcud data qorunur.
    }
};
