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
        Schema::create('mobilization_bonus_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->unique()->constrained('ranks')->onDelete('cascade');
            $table->unsignedTinyInteger('required_consecutive_periods')->default(2);
            $table->decimal('bonus_amount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobilization_bonus_tiers');
    }
};
