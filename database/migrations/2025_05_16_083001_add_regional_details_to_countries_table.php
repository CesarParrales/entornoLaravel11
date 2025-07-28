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
        Schema::table('countries', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->nullable()->after('currency_symbol')->comment('Phone country code, e.g., +593');
            $table->string('dni_label', 50)->nullable()->after('phone_country_code')->comment('Label for DNI/ID document, e.g., Cédula, RUC, DNI');
            $table->string('dni_format_regex')->nullable()->after('dni_label')->comment('Regex for DNI format validation');
            $table->unsignedTinyInteger('dni_fixed_length')->nullable()->after('dni_format_regex')->comment('Fixed length for DNI, if applicable');
            
            $table->string('phone_national_format_regex')->nullable()->after('dni_fixed_length')->comment('Regex for national phone number format');
            $table->unsignedTinyInteger('phone_national_fixed_length')->nullable()->after('phone_national_format_regex')->comment('Fixed length for national phone number, if applicable');
            
            $table->decimal('vat_rate', 5, 4)->nullable()->after('phone_national_fixed_length')->comment('VAT rate for the country, e.g., 0.15 for 15%');
            $table->string('vat_label', 50)->nullable()->default('IVA')->after('vat_rate')->comment('Label for VAT, e.g., IVA, GST, VAT');
            
            $table->string('default_language_code', 5)->nullable()->after('vat_label')->comment('Default language code, e.g., es, en');
            $table->string('default_timezone')->nullable()->after('default_language_code')->comment('Default timezone, e.g., America/Guayaquil');

            // Added administrative division labels
            $table->string('administrative_division_label_1')->nullable()->after('default_timezone')->comment('Label for 1st level admin division (e.g., Province, State)');
            $table->string('administrative_division_label_2')->nullable()->after('administrative_division_label_1')->comment('Label for 2nd level admin division (e.g., City, County)');
            $table->string('administrative_division_label_3')->nullable()->after('administrative_division_label_2')->comment('Label for 3rd level admin division (e.g., Parish, District)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'phone_country_code',
                'dni_label',
                'dni_format_regex',
                'dni_fixed_length',
                'phone_national_format_regex',
                'phone_national_fixed_length',
                'vat_rate',
                'vat_label',
                'default_language_code',
                'default_timezone',
                'administrative_division_label_1', // Added for rollback
                'administrative_division_label_2', // Added for rollback
                'administrative_division_label_3', // Added for rollback
            ]);
        });
    }
};
