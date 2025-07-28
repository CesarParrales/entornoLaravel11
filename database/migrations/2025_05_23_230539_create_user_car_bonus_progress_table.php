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
        Schema::create('user_car_bonus_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bonus_type_id')->constrained('bonus_types')->onDelete('cascade'); // Para el tipo de bono 'bono-auto'
            
            $table->unsignedInteger('current_cycle_number')->default(1)->comment('Número del ciclo actual del bono auto para el usuario.');
            $table->unsignedInteger('payments_made_this_cycle')->default(0)->comment('Número de pagos mensuales realizados en el ciclo actual.');
            $table->boolean('is_eligible_for_cycle')->default(false)->comment('Indica si el usuario ya desbloqueó su primer ciclo del bono auto.');
            $table->json('cycle_config_snapshot')->nullable()->comment('Snapshot de la configuración del bono (monto, cuotas) al inicio del ciclo actual.');
            // $table->timestamp('cycle_started_at')->nullable(); // Podría ser útil
            // $table->timestamp('last_payment_at')->nullable(); // Podría ser útil

            $table->timestamps();

            $table->unique(['user_id', 'bonus_type_id', 'current_cycle_number'], 'user_car_bonus_cycle_unique');
            $table->index(['user_id', 'bonus_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_car_bonus_progress');
    }
};
