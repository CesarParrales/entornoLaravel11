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
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('Nombre descriptivo del periodo, ej. Q1-EneA 2025');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open')->comment('Ej: open, processing_closure, closed, archived'); // Estado del periodo
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
