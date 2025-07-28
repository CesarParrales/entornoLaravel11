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
        Schema::create('user_period_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->foreignId('rank_id')->constrained()->onDelete('cascade');
            $table->timestamp('achieved_at')->comment('Fecha en que se calculó y asignó este rango para el periodo');
            $table->json('calculation_details')->nullable()->comment('Datos que contribuyeron al cálculo del rango, ej. puntos');
            $table->timestamps();

            $table->unique(['user_id', 'period_id'], 'user_period_rank_unique');
            $table->index('rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_period_ranks');
    }
};
