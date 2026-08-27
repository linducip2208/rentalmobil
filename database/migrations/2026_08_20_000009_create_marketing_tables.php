<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_vouchers', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name')->nullable();
            $t->text('description')->nullable();
            $t->enum('discount_type', ['percentage', 'fixed']);
            $t->decimal('discount_value', 12, 2);
            $t->unsignedSmallInteger('min_rental_days')->nullable();
            $t->decimal('max_discount', 12, 2)->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('voucher_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('voucher_id')->constrained('promo_vouchers')->cascadeOnDelete();
            $t->foreignId('booking_id')->nullable()->constrained('bookings')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->decimal('discount_amount', 12, 2);
            $t->timestamp('used_at')->nullable();
            $t->timestamps();
        });

        Schema::create('blog_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->longText('content');
            $t->text('excerpt')->nullable();
            $t->string('featured_image')->nullable();
            $t->foreignId('category_id')->nullable()->constrained('blog_categories')->restrictOnDelete();
            $t->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $t->boolean('is_published')->default(false);
            $t->timestamp('published_at')->nullable();
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->unsignedInteger('views')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('question');
            $t->text('answer');
            $t->string('category')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('subject')->nullable();
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->string('name')->nullable();
            $t->string('company')->nullable();
            $t->string('avatar')->nullable();
            $t->unsignedTinyInteger('rating')->default(5);
            $t->text('content');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('name')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('subscribed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('surge_pricing_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $t->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->string('name');
            $t->decimal('multiplier', 4, 2)->default(1.00);
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->time('start_time')->nullable();
            $t->time('end_time')->nullable();
            $t->json('days_of_week')->nullable();
            $t->unsignedSmallInteger('priority')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_pricing_rules');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('promo_vouchers');
    }
};
