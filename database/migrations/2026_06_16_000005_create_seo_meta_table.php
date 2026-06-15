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
        Schema::create('seo_meta', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('model_type');
            $table->ulid('model_id');
            $table->string('title')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->json('schema_markup')->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
