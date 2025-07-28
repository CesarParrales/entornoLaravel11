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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade')->comment('Denormalized for easier querying, linked to province\'s country.');
            $table->string('name');
            $table->unsignedBigInteger('geoname_id')->nullable()->unique()->comment('Optional GeoNames ID for this city.');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            // Unique constraint for province_id and name to avoid duplicate cities in the same province
            $table->unique(['province_id', 'name'], 'province_city_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
