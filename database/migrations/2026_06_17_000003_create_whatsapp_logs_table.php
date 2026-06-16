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
        Schema::create('whatsapp_logs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('to', 30);
            $table->string('template');
            $table->json('variables')->nullable();
            $table->string('status', 20)->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['to', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
