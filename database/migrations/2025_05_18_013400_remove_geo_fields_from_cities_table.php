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
        Schema::table('cities', function (Blueprint $table) {
            // La columna geoname_id tiene un índice unique.
            // Necesitamos eliminarlo antes de eliminar la columna.
            // La columna geoname_id tenía un ->unique() sin nombre explícito en la migración original.
            // Laravel lo nombraría 'cities_geoname_id_unique' por convención.
            if (Schema::hasColumn('cities', 'geoname_id')) {
                $table->dropUnique('cities_geoname_id_unique');
                $table->dropColumn('geoname_id');
            }
            
            if (Schema::hasColumn('cities', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('cities', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'geoname_id')) {
                $table->unsignedBigInteger('geoname_id')->nullable()->unique()->after('name')->comment('Optional GeoNames ID for this city.');
            }
            if (!Schema::hasColumn('cities', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('geoname_id');
            }
            if (!Schema::hasColumn('cities', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }
};