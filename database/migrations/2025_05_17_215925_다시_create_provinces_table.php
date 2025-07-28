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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable()->comment('ISO 3166-2 code for the province/state, or other official code.');
            $table->unsignedBigInteger('geoname_id')->nullable()->unique()->comment('Optional GeoNames ID for this administrative division.');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('code');
            // Unique constraint for country_id and name to avoid duplicate provinces in the same country
            $table->unique(['country_id', 'name'], 'country_province_name_unique');
            $table->unique(['country_id', 'code'], 'country_province_code_unique')->whereNotNull('code');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
