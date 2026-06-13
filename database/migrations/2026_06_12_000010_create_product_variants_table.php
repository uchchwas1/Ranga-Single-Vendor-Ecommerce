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
        Schema::create('product_variants', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('weight', 8, 3)->nullable();
            $table->foreignUlid('image_id')->nullable()->constrained('product_images')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
