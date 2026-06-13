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
        Schema::create('product_videos', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('video_url');
            $table->string('thumbnail')->nullable();
            $table->string('title')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_videos');
    }
};
