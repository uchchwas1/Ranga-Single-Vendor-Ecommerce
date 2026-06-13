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
        Schema::create('attribute_values', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->json('meta')->nullable(); // e.g. {"hex": "#ff0000"} for color attributes
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['attribute_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
