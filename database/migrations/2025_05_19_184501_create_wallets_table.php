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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency_code', 3)->default('USD');
            $table->string('status', 50)->default('active')->comment('e.g., active, suspended, frozen, closed');
            $table->decimal('min_balance_allowed', 15, 2)->nullable();
            $table->decimal('max_balance_allowed', 15, 2)->nullable();
            $table->timestamp('last_transaction_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
