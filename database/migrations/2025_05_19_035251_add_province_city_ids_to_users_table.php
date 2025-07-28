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
            // Make existing text fields nullable as they will be replaced by IDs
            $table->string('address_state')->nullable()->change(); // Provincia
            $table->string('address_city')->nullable()->change();  // Ciudad

            // Add new foreign ID columns
            $table->foreignId('address_province_id')->nullable()->after('address_country_id')->constrained('provinces')->nullOnDelete();
            $table->foreignId('address_city_id')->nullable()->after('address_province_id')->constrained('cities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['address_province_id']);
            $table->dropForeign(['address_city_id']);
            $table->dropColumn(['address_province_id', 'address_city_id']);

            // Revert nullability if needed, though data might be inconsistent if previously text was used
            // For simplicity, we'll leave them nullable on down, or you could restore previous state if known
            // $table->string('address_state')->nullable(false)->change(); 
            // $table->string('address_city')->nullable(false)->change();
        });
    }
};
