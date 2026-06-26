<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aa_page_builder_blocks') && ! Schema::hasColumn('aa_page_builder_blocks', 'block_uuid')) {
            Schema::table('aa_page_builder_blocks', function (Blueprint $table): void {
                $table->uuid('block_uuid')->nullable()->after('id')->index();
                $table->longText('content_json')->nullable()->after('body');
            });

            DB::table('aa_page_builder_blocks')->orderBy('id')->get(['id'])->each(
                fn ($row) => DB::table('aa_page_builder_blocks')->where('id', $row->id)->update(['block_uuid' => (string) Str::uuid()])
            );
        }

        if (! Schema::hasTable('aa_page_publications')) {
            Schema::create('aa_page_publications', function (Blueprint $table): void {
                $table->id();
                $table->string('lang_code', 10);
                $table->string('page_key', 120);
                $table->unsignedInteger('version')->default(1);
                $table->longText('blocks_json');
                $table->foreignId('published_by')->nullable()->constrained('aa_admin_users')->nullOnDelete();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->unique(['lang_code', 'page_key']);
            });
        }

        if (! Schema::hasTable('aa_page_revisions')) {
            Schema::create('aa_page_revisions', function (Blueprint $table): void {
                $table->id();
                $table->string('lang_code', 10);
                $table->string('page_key', 120);
                $table->unsignedInteger('version');
                $table->longText('blocks_json');
                $table->string('change_note')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('aa_admin_users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['lang_code', 'page_key', 'version']);
            });
        }

        if (Schema::hasTable('aa_page_builder_blocks')) {
            DB::table('aa_page_builder_blocks')->select('lang_code', 'page_key')->distinct()->get()->each(function ($page): void {
                if (DB::table('aa_page_publications')->where('lang_code', $page->lang_code)->where('page_key', $page->page_key)->exists()) {
                    return;
                }

                $blocks = DB::table('aa_page_builder_blocks')
                    ->where('lang_code', $page->lang_code)->where('page_key', $page->page_key)
                    ->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->map(fn ($block) => (array) $block)->all();
                $json = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                DB::table('aa_page_publications')->insert([
                    'lang_code' => $page->lang_code, 'page_key' => $page->page_key, 'version' => 1,
                    'blocks_json' => $json, 'published_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('aa_page_revisions')->insert([
                    'lang_code' => $page->lang_code, 'page_key' => $page->page_key, 'version' => 1,
                    'blocks_json' => $json, 'change_note' => 'Initial migration', 'created_at' => now(), 'updated_at' => now(),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aa_page_revisions');
        Schema::dropIfExists('aa_page_publications');
        if (Schema::hasTable('aa_page_builder_blocks')) {
            Schema::table('aa_page_builder_blocks', function (Blueprint $table): void {
                $table->dropColumn(['block_uuid', 'content_json']);
            });
        }
    }
};
