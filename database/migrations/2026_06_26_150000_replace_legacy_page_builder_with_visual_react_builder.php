<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aa_visual_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 8);
            $table->string('page_key', 120);
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('template', 120)->nullable();
            $table->unsignedBigInteger('active_revision_id')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
            $table->unique(['lang_code', 'page_key']);
            $table->index(['lang_code', 'is_archived', 'is_deleted']);
        });

        Schema::create('aa_visual_page_revisions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedInteger('revision_number');
            $table->string('status', 20);
            $table->unsignedInteger('editor_revision')->default(0);
            $table->json('document_json');
            $table->json('theme_settings')->nullable();
            $table->string('change_note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['page_id', 'revision_number']);
            $table->index(['page_id', 'status']);
        });

        Schema::create('aa_visual_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('disk', 40)->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index(['is_deleted', 'created_at']);
        });

        Schema::create('aa_visual_page_revision_assets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('revision_id');
            $table->unsignedBigInteger('asset_id');
            $table->timestamp('created_at')->nullable();
            $table->unique(['revision_id', 'asset_id']);
            $table->index('asset_id');
        });

        Schema::create('aa_visual_page_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event', 80);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aa_visual_page_activities');
        Schema::dropIfExists('aa_visual_page_revision_assets');
        Schema::dropIfExists('aa_visual_assets');
        Schema::dropIfExists('aa_visual_page_revisions');
        Schema::dropIfExists('aa_visual_pages');
    }
};
