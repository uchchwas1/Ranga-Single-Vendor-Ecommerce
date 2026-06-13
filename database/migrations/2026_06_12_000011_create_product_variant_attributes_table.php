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
        Schema::create('product_variant_attributes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignUlid('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignUlid('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['variant_id', 'attribute_id'], 'variant_attribute_unique');
            $table->index('attribute_value_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_attributes');
    }
};
