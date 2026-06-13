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
        Schema::create('inventory_logs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->string('type', 20); // purchase/sale/adjustment/return
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type')->nullable();
            $table->ulid('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['inventory_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
