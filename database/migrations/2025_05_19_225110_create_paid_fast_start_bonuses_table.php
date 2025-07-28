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
        Schema::create('paid_fast_start_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('new_socio_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bonus_type_id')->constrained('bonus_types')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->timestamp('paid_at');
            $table->timestamps(); // created_at and updated_at

            // Index for faster lookups to prevent duplicate payments
            $table->unique(['beneficiary_id', 'new_socio_id', 'bonus_type_id'], 'unique_fast_start_bonus_payment');
            // Optional: Index on order_id if you need to query by it frequently
            // $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_fast_start_bonuses');
    }
};
