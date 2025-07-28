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
        Schema::create('user_earned_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bonus_type_id')->constrained('bonus_types')->onDelete('cascade'); // Para identificar qué bono otorgó el premio
            $table->text('award_description')->comment('Descripción del premio ganado, ej. Viaje Anual 2025');
            $table->timestamp('awarded_at')->comment('Fecha y hora en que se otorgó el premio');
            $table->string('status')->default('pending_claim')->comment('Estado del premio: pending_claim, claimed, fulfilled, expired, etc.');
            $table->text('notes')->nullable()->comment('Notas adicionales del administrador o del sistema.');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_earned_awards');
    }
};
