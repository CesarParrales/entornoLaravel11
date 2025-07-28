<?php

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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('pays_bonus')->default(false)->after('promotion_end_date');
            $table->decimal('bonus_amount', 8, 2)->nullable()->default(0)->after('pays_bonus');
            $table->integer('min_configurable_items')->nullable()->default(1)->after('bonus_amount');
            $table->integer('max_configurable_items')->nullable()->after('min_configurable_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['pays_bonus', 'bonus_amount', 'min_configurable_items', 'max_configurable_items']);
        });
    }
};
