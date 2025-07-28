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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id(); // Primary key

            // Datos de la empresa
            $table->string('ruc')->unique()->nullable();
            $table->string('legal_name')->nullable(); // Razón Social
            $table->string('commercial_name')->nullable(); // Nombre Comercial
            $table->string('logo_platform_light_path')->nullable();
            $table->string('logo_platform_dark_path')->nullable();
            $table->string('logo_invoicing_path')->nullable();
            $table->string('favicon_path')->nullable();

            // Ubicación
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->text('address')->nullable();

            // Facturación
            $table->boolean('is_special_taxpayer')->default(false);
            $table->unsignedInteger('invoice_sequence_start')->default(1);
            $table->decimal('vat_rate', 5, 2)->default(0.00); // Porcentaje, ej. 15.00 para 15%
            $table->string('invoice_establishment_code', 3)->default('001');
            $table->string('invoice_emission_point_code', 3)->default('001');

            // Contacto
            $table->string('phone_fixed')->nullable();
            $table->string('phone_mobile')->nullable();
            $table->string('email_primary')->nullable();
            $table->string('email_secondary')->nullable();
            $table->json('social_media_links')->nullable(); // Ej: {"facebook": "url", "instagram": "url", "tiktok": "url", "youtube": "url", "linkedin": "url"}

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
