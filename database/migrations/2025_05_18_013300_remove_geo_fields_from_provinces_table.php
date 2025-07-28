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
        Schema::table('provinces', function (Blueprint $table) {
            // Intentar eliminar índices por las columnas que los definen o por nombre conocido.
            // Laravel intentará adivinar el nombre del índice si solo se pasan las columnas.
            // El índice unique ['country_id', 'code'] fue nombrado 'country_province_code_unique'.
            // El índice simple en 'code' se llamaría 'provinces_code_index' por convención.
            
            // Para estar seguros, verificamos si la columna 'code' existe antes de intentar eliminar índices sobre ella.
            if (Schema::hasColumn('provinces', 'code')) {
                // Primero el índice unique compuesto
                // Es más seguro referenciarlo por su nombre explícito si se conoce.
                // Si no, $table->dropUnique(['country_id', 'code']); podría funcionar.
                // Dado que la migración original lo nombró, usamos el nombre.
                $table->dropUnique('country_province_code_unique');
                
                // Luego el índice simple en 'code'
                $table->dropIndex('provinces_code_index'); // Laravel nombra así: nombredetabla_nombresdecolumnas_index
            }

            // Ahora eliminar las columnas
            if (Schema::hasColumn('provinces', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('provinces', 'geoname_id')) {
                // geoname_id también tenía un ->unique() sin nombre explícito,
                // Laravel lo nombraría 'provinces_geoname_id_unique'
                $table->dropUnique('provinces_geoname_id_unique');
                $table->dropColumn('geoname_id');
            }
            if (Schema::hasColumn('provinces', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('provinces', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            // Re-añadir las columnas con sus definiciones originales
            // Es importante que coincidan con la migración de creación
            if (!Schema::hasColumn('provinces', 'code')) {
                $table->string('code')->nullable()->after('name')->comment('ISO 3166-2 code for the province/state, or other official code.');
            }
            if (!Schema::hasColumn('provinces', 'geoname_id')) {
                $table->unsignedBigInteger('geoname_id')->nullable()->unique()->after('code')->comment('Optional GeoNames ID for this administrative division.');
            }
            if (!Schema::hasColumn('provinces', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('geoname_id');
            }
            if (!Schema::hasColumn('provinces', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            // Re-añadir los índices
            $table->index('code'); // Nombre por defecto: provinces_code_index
            $table->unique(['country_id', 'code'], 'country_province_code_unique')->whereNotNull('code');
        });
    }
};