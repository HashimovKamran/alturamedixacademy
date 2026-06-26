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
        Schema::table('aa_page_builder_blocks', function (Blueprint $table): void {
            if (! Schema::hasColumn('aa_page_builder_blocks', 'parent_block_uuid')) {
                $table->uuid('parent_block_uuid')->nullable()->after('block_uuid')->index();
                $table->string('slot_key', 80)->default('default')->after('parent_block_uuid');
                $table->string('region_key', 80)->default('main')->after('page_key');
                $table->unsignedSmallInteger('schema_version')->default(1)->after('block_type');
                $table->unsignedBigInteger('pattern_source_id')->nullable()->after('schema_version')->index();
            }
        });

        if (! Schema::hasTable('aa_block_patterns')) {
            Schema::create('aa_block_patterns', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('category', 120)->default('general')->index();
                $table->string('root_type', 80);
                $table->longText('blocks_json');
                $table->foreignId('created_by')->nullable()->constrained('aa_admin_users')->nullOnDelete();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        $languages = Schema::hasTable('aa_languages')
            ? DB::table('aa_languages')->where('is_active', true)->pluck('code')->all()
            : ['az'];
        $pageTypes = [
            '__header' => 'native_header',
            'index' => 'native_home',
            'about' => 'native_page',
            'articles' => 'native_articles',
            'article_detail' => 'native_article',
            'certificates' => 'native_certificates',
            'trainings' => 'native_trainings',
            'gallery' => 'native_gallery',
            'contact' => 'native_page',
            'profile' => 'native_profile',
            '__footer' => 'native_footer',
        ];

        foreach ($languages as $language) {
            foreach ($pageTypes as $pageKey => $type) {
                if (! DB::table('aa_page_builder_blocks')->where('lang_code', $language)->where('page_key', $pageKey)->exists()) {
                    DB::table('aa_page_builder_blocks')->insert([
                        'block_uuid' => (string) Str::uuid(),
                        'parent_block_uuid' => null,
                        'lang_code' => $language,
                        'page_key' => $pageKey,
                        'region_key' => str_starts_with($pageKey, '__') ? 'template' : 'main',
                        'block_type' => $type,
                        'schema_version' => 1,
                        'title' => null,
                        'subtitle' => null,
                        'body' => null,
                        'content_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                        'image_path' => null,
                        'button_text' => null,
                        'button_url' => null,
                        'settings_json' => json_encode(['theme' => 'surface', 'layout' => 'wide'], JSON_UNESCAPED_UNICODE),
                        'slot_key' => 'default',
                        'sort_order' => 1,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $rows = DB::table('aa_page_builder_blocks')
                    ->where('lang_code', $language)->where('page_key', $pageKey)
                    ->orderBy('sort_order')->orderBy('id')->get()
                    ->map(fn ($row) => (array) $row)->all();
                $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $current = DB::table('aa_page_publications')->where('lang_code', $language)->where('page_key', $pageKey)->first();
                $version = $current ? ((int) $current->version + 1) : 1;

                DB::table('aa_page_publications')->updateOrInsert(
                    ['lang_code' => $language, 'page_key' => $pageKey],
                    ['version' => $version, 'blocks_json' => $json, 'published_at' => now(), 'created_at' => $current?->created_at ?? now(), 'updated_at' => now()]
                );
                DB::table('aa_page_revisions')->insert([
                    'lang_code' => $language, 'page_key' => $pageKey, 'version' => $version,
                    'blocks_json' => $json, 'change_note' => 'Phase 4 structured composition migration',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aa_block_patterns');
        Schema::table('aa_page_builder_blocks', function (Blueprint $table): void {
            $table->dropColumn(['parent_block_uuid', 'slot_key', 'region_key', 'schema_version', 'pattern_source_id']);
        });
    }
};
