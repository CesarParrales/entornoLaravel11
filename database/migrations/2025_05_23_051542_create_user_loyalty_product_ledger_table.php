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
        Schema::create('user_loyalty_product_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type')->comment('ej: earned, redeemed, correction_add, correction_subtract');
            $table->integer('products_quantity')->comment('Cantidad de productos para esta transacción (positivo o negativo)');
            $table->integer('balance_after_transaction')->comment('Saldo de productos de lealtad del usuario después de esta transacción');
            $table->text('notes')->nullable();
            
            $table->foreignId('source_bonus_type_id')->nullable()->constrained('bonus_types')->onDelete('set null');
            $table->foreignId('source_period_id')->nullable()->constrained('periods')->onDelete('set null');
            $table->foreignId('source_order_id')->nullable()->constrained('orders')->onDelete('set null'); // Para canjes

            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_loyalty_product_ledger');
    }
};
