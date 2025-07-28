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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('iso_code_2', 2)->unique();
            $table->string('iso_code_3', 3)->unique()->nullable();
            $table->string('currency_code', 3);
            $table->string('currency_symbol', 5)->nullable();
            $table->decimal('usd_exchange_rate', 15, 6)->nullable()->comment('Exchange rate against USD, for informational display.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
