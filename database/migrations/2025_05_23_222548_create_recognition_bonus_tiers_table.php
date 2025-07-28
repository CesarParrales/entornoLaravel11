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
        Schema::create('recognition_bonus_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->unique()->constrained('ranks')->onDelete('cascade');
            $table->unsignedTinyInteger('annual_periods_required')->comment('Número de periodos de cierre en el año que se debe mantener el rango o superior.');
            $table->decimal('bonus_amount', 12, 2); // Monto del bono, ej: 1000.00
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recognition_bonus_tiers');
    }
};
