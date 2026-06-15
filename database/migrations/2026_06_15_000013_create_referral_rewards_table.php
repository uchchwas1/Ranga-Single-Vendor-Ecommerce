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
        Schema::create('referral_rewards', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reward_type', 20); // points/fixed
            $table->decimal('reward_value', 12, 2);
            $table->string('status', 20)->default('pending'); // pending/granted/expired
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
