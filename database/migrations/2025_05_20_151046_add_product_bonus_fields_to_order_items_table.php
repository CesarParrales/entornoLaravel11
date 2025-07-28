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
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('product_pays_bonus_at_purchase')->default(false)->after('points_value_at_purchase');
            $table->decimal('product_bonus_amount_at_purchase', 10, 2)->nullable()->after('product_pays_bonus_at_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('product_bonus_amount_at_purchase');
            $table->dropColumn('product_pays_bonus_at_purchase');
        });
    }
};
