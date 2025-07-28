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
        Schema::table('countries', function (Blueprint $table) {
            $table->unsignedBigInteger('geoname_id')->nullable()->unique()->after('iso_code_3')->comment('Optional GeoNames ID for this country.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Check if the unique index exists before trying to drop it by convention.
            // Laravel < 9 might require explicit index name. For Laravel 9+ unique() creates a named index.
            // $table->dropUnique('countries_geoname_id_unique'); // Default convention if not specified
            $table->dropColumn('geoname_id');
        });
    }
};
