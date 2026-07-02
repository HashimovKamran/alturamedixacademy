<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_builder_assets', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('disk', 64);
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('extension', 16);
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 500)->nullable();
            $table->string('checksum', 64)->index();
            $table->unsignedBigInteger('uploaded_by_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_builder_assets');
    }
};
