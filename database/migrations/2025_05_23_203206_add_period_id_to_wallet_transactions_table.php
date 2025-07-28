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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('period_id')
                  ->nullable()
                  ->after('sourceable_type') // O después de la columna que consideres más apropiada
                  ->constrained('periods')
                  ->onDelete('set null'); // Si se borra un periodo, la transacción no se borra, solo se desvincula el period_id
            
            $table->index('period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Para eliminar la FK y la columna, es mejor hacerlo en este orden
            // si el nombre de la restricción es el por defecto.
            // Si se especificó un nombre para la FK, usar ese nombre.
            // $table->dropForeign(['period_id']); // O $table->dropForeign('wallet_transactions_period_id_foreign');
            $table->dropConstrainedForeignId('period_id'); // Laravel 9+
            // $table->dropColumn('period_id'); // dropConstrainedForeignId ya elimina la columna también
        });
    }
};
