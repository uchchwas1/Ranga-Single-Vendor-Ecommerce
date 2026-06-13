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
        Schema::create('stock_alerts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
