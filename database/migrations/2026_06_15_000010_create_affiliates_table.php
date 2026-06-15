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
        Schema::create('affiliates', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->decimal('commission_rate', 8, 2)->default(0);
            $table->string('commission_type', 20)->default('percent'); // percent/fixed
            $table->decimal('earnings_total', 14, 2)->default(0);
            $table->decimal('paid_total', 14, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending/active/suspended
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
