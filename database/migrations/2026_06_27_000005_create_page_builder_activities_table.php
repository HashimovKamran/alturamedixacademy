<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_builder_activities', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('page_id')->index();
            $table->ulid('revision_id')->nullable()->index();
            $table->string('action', 100);
            $table->json('properties')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_builder_activities');
    }
};
