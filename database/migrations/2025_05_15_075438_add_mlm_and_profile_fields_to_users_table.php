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
        Schema::table('users', function (Blueprint $table) {
            // Campos añadidos para el perfil y MLM
            $table->string('first_name')->nullable()->after('name')->comment('Nombres del usuario');
            $table->string('last_name')->nullable()->after('first_name')->comment('Apellidos del usuario');
            $table->string('phone')->nullable()->after('email')->comment('Número de teléfono principal');
            
            $table->string('username')->unique()->nullable()->after('last_name')->comment('Nombre de usuario único para login y perfil público');
            $table->date('birth_date')->nullable()->comment('Fecha de nacimiento');
            $table->string('gender')->nullable()->comment('Género del usuario');
            $table->string('dni')->nullable()->unique()->comment('Documento Nacional de Identidad o RUC (renombrado de dni_ruc)');
            
            // Campos MLM
            $table->unsignedBigInteger('sponsor_id')->nullable()->comment('ID del patrocinador en la red MLM');
            $table->foreign('sponsor_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unsignedBigInteger('referrer_id')->nullable()->comment('ID del usuario que invitó/refirió (renombrado de invitador_id)');
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('placement_id')->nullable()->comment('ID del usuario bajo el cual está posicionado en la red (para unilevel puede ser igual a sponsor)');
            $table->foreign('placement_id')->references('id')->on('users')->onDelete('set null');

            $table->string('binary_position', 10)->nullable()->comment('Posición en la red binaria (L o R), si aplica');
            $table->integer('mlm_level')->default(0)->comment('Nivel del usuario en la estructura MLM');
            
            // Estado y otros
            $table->string('status')->default('pending_approval')->comment('Estado del usuario: pending_approval, active, inactive, suspended');
            $table->boolean('profile_completed')->default(false)->comment('Indica si el perfil del usuario tiene la información mínima completa');
            $table->boolean('agreed_to_terms')->default(false)->comment('Indica si el usuario aceptó los términos y condiciones');

            // Campos que estaban en la versión anterior de esta migración, algunos se renombran o ajustan
            // $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null')->comment('ID del país del usuario'); // Se maneja en add_address_fields_to_users_table
            // $table->timestamp('activated_at')->nullable()->comment('Fecha y hora de activación de la cuenta'); // Reemplazado por status y email_verified_at
            // $table->timestamp('archived_at')->nullable()->comment('Fecha y hora de archivado de la cuenta'); // Reemplazado por status
            $table->string('avatar_path')->nullable()->comment('Ruta al archivo de imagen del avatar');
            $table->string('civil_status')->nullable()->comment('Estado civil del usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sponsor_id']);
            $table->dropForeign(['referrer_id']);
            $table->dropForeign(['placement_id']);
            // $table->dropForeign(['country_id']); // Se maneja en add_address_fields_to_users_table

            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'username',
                'birth_date',
                'gender',
                'dni',
                'sponsor_id',
                'referrer_id',
                'placement_id',
                'binary_position',
                'mlm_level',
                'status',
                'profile_completed',
                'agreed_to_terms',
                // 'activated_at',
                // 'archived_at',
                'avatar_path',
                'civil_status',
            ]);
        });
    }
};
