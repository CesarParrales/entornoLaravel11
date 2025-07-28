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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('rank_order')->unique()->comment('Order of progression for ranks');
            $table->integer('required_group_volume')->default(0)->comment('Total group volume points required');
            // $table->integer('required_personal_points')->default(0)->comment('Personal points required for the rank (distinct from instant qualification)'); // Decided against for now
            $table->integer('required_direct_sponsors_count')->default(0)->comment('Number of direct sponsors required');
            $table->foreignId('required_direct_sponsor_rank_id')->nullable()->constrained('ranks')->nullOnDelete()->comment('Minimum rank ID required for direct sponsors');
            $table->integer('compression_depth_level')->nullable()->comment('Depth for dynamic compression to find qualified sponsors');
            $table->integer('instant_qualification_personal_points')->nullable()->comment('Personal points for instant qualification to this rank');
            
            $table->decimal('leg_alpha_min_percentage_vg', 5, 2)->nullable()->comment('Min VG percentage from Alpha leg (e.g., 0.50 for 50%)');
            $table->decimal('leg_beta_min_percentage_vg', 5, 2)->nullable()->comment('Min VG percentage from Beta leg (e.g., 0.50 for 50%)');
            // Consider adding more leg rules if needed in the future, e.g., max percentage from one leg, or number of qualifying legs.
            // For now, Alpha and Beta percentages cover the 50/50 example for Bronze.

            $table->boolean('is_active')->default(true);
            $table->string('color_badge')->nullable()->comment('Tailwind color class or HEX for badge');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
