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
        Schema::create('financial_freedom_commission_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->unique()->constrained('ranks')->onDelete('cascade');
            $table->unsignedInteger('max_points_for_rank');
            $table->decimal('percentage', 5, 4); // Ej. 0.1400 para 14.00%
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_freedom_commission_tiers');
    }
};
