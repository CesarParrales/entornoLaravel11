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
        Schema::create('product_bundle_configurable_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('option_product_id')->constrained('products')->onDelete('cascade');
            $table->integer('default_quantity')->nullable()->default(1); // Example: if this option is chosen, it comes with this quantity by default, or max quantity for this specific option line
            $table->timestamps();

            // Optional: Add a unique constraint to prevent duplicate option entries for the same bundle
            // $table->unique(['bundle_product_id', 'option_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundle_configurable_options');
    }
};
