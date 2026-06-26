<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aa_languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('title');
            $table->string('native_name')->nullable();
            $table->string('locale', 20)->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('aa_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('setting_key')->index();
            $table->longText('setting_value')->nullable();
            $table->timestamps();
            $table->unique(['lang_code', 'setting_key']);
        });

        Schema::create('aa_admin_users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('full_name')->default('Admin');
            $table->string('password_hash');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aa_site_users', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('password_hash')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar_url', 700)->nullable();
            $table->boolean('email_notify')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aa_menus', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->foreignId('parent_id')->nullable()->constrained('aa_menus')->nullOnDelete();
            $table->string('title');
            $table->string('url', 700)->default('#');
            $table->string('target', 20)->default('_self');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['lang_code', 'parent_id', 'sort_order']);
        });

        Schema::create('aa_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('page_key', 120)->index();
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->string('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_path', 700)->nullable();
            $table->string('cover_image', 700)->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 700)->nullable();
            $table->string('meta_description', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['lang_code', 'page_key']);
        });

        Schema::create('aa_sliders', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->string('image_path', 700)->nullable();
            $table->string('button_1_text')->nullable();
            $table->string('button_1_url', 700)->nullable();
            $table->string('button_2_text')->nullable();
            $table->string('button_2_url', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_home_stats', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('icon_class')->nullable();
            $table->string('number_text');
            $table->string('title');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_article_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('title');
            $table->string('slug');
            $table->string('icon_class')->nullable();
            $table->string('image_path', 700)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['lang_code', 'slug']);
        });

        Schema::create('aa_articles', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->foreignId('category_id')->nullable()->constrained('aa_article_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image', 700)->nullable();
            $table->string('author_name')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['lang_code', 'slug']);
        });

        Schema::create('aa_trainings', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('title');
            $table->string('location')->nullable();
            $table->date('training_date')->nullable()->index();
            $table->string('register_url', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_features', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon_class')->nullable();
            $table->string('url', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('block_key', 120)->index();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->string('image_path', 700)->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['lang_code', 'block_key']);
        });

        Schema::create('aa_partners', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('title');
            $table->string('logo_path', 700)->nullable();
            $table->string('url', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_ads', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('position_key', 80)->default('sidebar')->index();
            $table->string('title')->nullable();
            $table->string('image_path', 700)->nullable();
            $table->string('url', 700)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_gallery', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path', 700);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('aa_certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('cert_no')->unique();
            $table->string('lang_code', 10)->index();
            $table->string('full_name');
            $table->string('course_title');
            $table->string('certificate_type', 80)->default('certificate');
            $table->date('issue_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('file_path', 700)->nullable();
            $table->string('status', 80)->default('valid')->index();
            $table->longText('note')->nullable();
            $table->timestamps();
        });

        Schema::create('aa_contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message');
            $table->string('ip_address', 64)->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('aa_admin_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('aa_admin_users')->nullOnDelete();
            $table->string('admin_name')->nullable();
            $table->string('module', 80)->index();
            $table->string('action', 80)->index();
            $table->longText('description')->nullable();
            $table->string('object_type', 80)->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 700)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('aa_article_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('aa_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('aa_site_users')->nullOnDelete();
            $table->string('email');
            $table->string('status', 80)->default('pending')->index();
            $table->longText('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['article_id', 'email']);
        });

        Schema::create('aa_page_builder_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 10)->index();
            $table->string('page_key', 120)->index();
            $table->string('block_type', 80)->default('text');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->string('image_path', 700)->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 700)->nullable();
            $table->longText('settings_json')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['lang_code', 'page_key', 'sort_order']);
        });

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

    public function down(): void
    {
        Schema::dropIfExists('aa_visual_blocks');
        Schema::dropIfExists('aa_visual_edits');
        Schema::dropIfExists('aa_page_builder_blocks');
        Schema::dropIfExists('aa_article_notifications');
        Schema::dropIfExists('aa_admin_logs');
        Schema::dropIfExists('aa_contact_messages');
        Schema::dropIfExists('aa_certificates');
        Schema::dropIfExists('aa_gallery');
        Schema::dropIfExists('aa_ads');
        Schema::dropIfExists('aa_partners');
        Schema::dropIfExists('aa_blocks');
        Schema::dropIfExists('aa_features');
        Schema::dropIfExists('aa_trainings');
        Schema::dropIfExists('aa_articles');
        Schema::dropIfExists('aa_article_categories');
        Schema::dropIfExists('aa_home_stats');
        Schema::dropIfExists('aa_sliders');
        Schema::dropIfExists('aa_pages');
        Schema::dropIfExists('aa_menus');
        Schema::dropIfExists('aa_site_users');
        Schema::dropIfExists('aa_admin_users');
        Schema::dropIfExists('aa_settings');
        Schema::dropIfExists('aa_languages');
    }
};