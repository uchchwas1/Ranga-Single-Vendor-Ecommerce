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
        Schema::create('shipping_methods', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('carrier')->nullable();
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->decimal('max_weight', 8, 3)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
