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
        Schema::create('popups', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('content')->nullable();
            $table->string('trigger_type', 20)->default('delay');
            $table->unsignedInteger('trigger_delay')->default(0);
            $table->boolean('show_once')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
