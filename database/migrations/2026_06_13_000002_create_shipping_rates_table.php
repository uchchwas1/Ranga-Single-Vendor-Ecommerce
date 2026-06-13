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
        Schema::create('shipping_rates', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('shipping_method_id')->constrained('shipping_methods')->cascadeOnDelete();
            // shipping_zone_id is left nullable; zones are introduced in a later phase.
            $table->ulid('shipping_zone_id')->nullable()->index();
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->decimal('per_kg_rate', 12, 2)->default(0);
            $table->decimal('free_above_amount', 12, 2)->nullable();
            $table->unsignedInteger('estimated_days_min')->default(1);
            $table->unsignedInteger('estimated_days_max')->default(5);
            $table->timestamps();

            $table->index(['shipping_method_id', 'shipping_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
