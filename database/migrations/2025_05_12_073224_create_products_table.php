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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('product_type')->default('simple')->comment("Enum: 'simple', 'bundle_fixed', 'bundle_configurable'");
            
            $table->decimal('base_price', 15, 2)->comment('Base price in USD');
            $table->unsignedInteger('points_value')->default(0);

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            // $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null'); // Uncomment if you add brands

            // Promotion fields
            $table->boolean('is_on_promotion')->default(false);
            $table->decimal('promotional_price', 15, 2)->nullable()->comment('Promotional price in USD');
            $table->timestamp('promotion_start_date')->nullable();
            $table->timestamp('promotion_end_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // If you want soft deletes for products
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
