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
                $table->text('ingredients')->nullable()->after('short_description');
                $table->text('properties')->nullable()->after('ingredients');
                $table->text('content_details')->nullable()->after('properties')->comment('Content and specifications');
                $table->string('main_image_path')->nullable()->after('content_details');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['ingredients', 'properties', 'content_details', 'main_image_path']);
            });
        }
    };
