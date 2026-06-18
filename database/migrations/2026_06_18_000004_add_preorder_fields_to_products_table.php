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
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_preorder')->default(false)->after('is_digital');
            $table->timestamp('preorder_available_at')->nullable()->after('is_preorder');
            $table->string('preorder_payment', 10)->nullable()->after('preorder_available_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['is_preorder', 'preorder_available_at', 'preorder_payment']);
        });
    }
};
