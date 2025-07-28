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
        Schema::table('ranks', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name')->comment('Identificador único para el rango, usado internamente.');
            // Nullable temporalmente para poder actualizar los existentes, luego se puede hacer no nulable si se desea.
            // O se puede poblar con un valor por defecto y luego actualizar.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
