<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUlid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->json('specifications')->nullable();
            $table->json('faqs')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('status', 20)->default('draft'); // draft/active/scheduled/archived
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->decimal('weight', 8, 3)->nullable();
            $table->string('weight_unit', 10)->default('kg');
            $table->json('dimensions')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_featured');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('created_at');
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
