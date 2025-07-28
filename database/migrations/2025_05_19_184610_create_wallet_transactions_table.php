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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Denormalized for easier querying by user
            
            $table->uuid('transaction_uuid')->unique();
            $table->string('type', 50)->comment('e.g., credit, debit, commission_payout, bonus_payout, withdrawal_request, etc.');
            $table->decimal('amount', 15, 2); // Always positive
            $table->decimal('balance_before_transaction', 15, 2);
            $table->decimal('balance_after_transaction', 15, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->string('description');
            
            $table->unsignedBigInteger('sourceable_id')->nullable();
            $table->string('sourceable_type')->nullable();
            
            $table->json('metadata')->nullable();
            $table->string('status', 50)->default('completed')->comment('e.g., pending, completed, failed, cancelled, reversed');
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index(['sourceable_id', 'sourceable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
