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
        Schema::create('bonus_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique()->comment('Identifier for programmatic use');
            $table->boolean('is_active')->default(true);
            
            $table->string('calculation_type')->comment('e.g., fixed_amount, percentage_of_purchase, points_to_currency');
            $table->decimal('amount_fixed', 15, 2)->nullable()->comment('For fixed_amount type');
            $table->decimal('percentage_value', 5, 2)->nullable()->comment('For percentage_of_purchase type (e.g., 0.05 for 5%)');
            $table->decimal('points_to_currency_conversion_factor', 15, 4)->nullable()->comment('For points_to_currency type (e.g., 1 point = X currency units)');
            
            $table->string('trigger_event')->comment('Event that triggers this bonus calculation');
            $table->json('configuration_details')->nullable()->comment('Specific rules, like min_order_points, min_user_rank_id, eligible_products etc.');
            $table->string('wallet_transaction_description_template')->nullable()->comment('Template for wallet transaction, e.g., "Bonus {BONUS_NAME} for {EVENT_DETAILS}"');
            
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
            $table->index('trigger_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_types');
    }
};
