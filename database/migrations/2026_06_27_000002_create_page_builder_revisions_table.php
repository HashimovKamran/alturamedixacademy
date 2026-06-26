<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_builder_revisions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('page_id')->index();
            $table->unsignedInteger('revision_number');
            $table->string('status', 16)->index();
            $table->json('document');
            $table->json('theme_settings')->nullable();
            $table->string('content_hash', 64);
            $table->unsignedInteger('editor_revision')->default(1);
            $table->unsignedBigInteger('created_by_id')->nullable()->index();
            $table->unsignedBigInteger('published_by_id')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['page_id', 'revision_number']);
            $table->index(['page_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_builder_revisions');
    }
};
