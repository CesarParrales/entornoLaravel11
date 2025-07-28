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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_registration_bundle')->default(false)->after('max_configurable_items');
            $table->decimal('registration_bundle_price', 10, 2)->nullable()->after('is_registration_bundle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_registration_bundle', 'registration_bundle_price']);
        });
    }
};
