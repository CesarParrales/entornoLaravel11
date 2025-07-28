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
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_street')->nullable()->comment('Calle y número de la dirección'); // Renombrado de address_line_1
            $table->string('address_city')->nullable()->comment('Ciudad de la dirección'); // Renombrado de city para consistencia
            $table->string('address_state')->nullable()->comment('Provincia o estado de la dirección'); // Renombrado de province
            $table->string('address_postal_code')->nullable()->comment('Código postal de la dirección'); // Añadido
            
            $table->unsignedBigInteger('address_country_id')->nullable()->comment('ID del país de la dirección'); // Añadido
            $table->foreign('address_country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['address_country_id']); // Añadido para el rollback
            $table->dropColumn([
                'address_street', 
                'address_city', 
                'address_state',
                'address_postal_code', // Añadido para el rollback
                'address_country_id' // Añadido para el rollback
            ]);
        });
    }
};
