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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Cliente registrado, nulo para invitados
            
            // Información del cliente (puede ser redundante si user_id está presente, pero útil para invitados o para snapshot)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            // Dirección de Envío
            $table->text('shipping_address_line1')->nullable();
            $table->text('shipping_address_line2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable(); // O provincia
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_country_code', 2)->nullable(); // Código ISO del país, ej. EC

            // Dirección de Facturación (opcional, si es diferente del envío)
            // $table->text('billing_address_line1')->nullable();
            // ... (campos similares para facturación)

            // Detalles financieros
            $table->decimal('subtotal', 10, 2); // Suma de los precios de los ítems
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('taxes', 10, 2)->default(0); // Podría ser un campo JSON si los impuestos son complejos
            $table->decimal('discount_amount', 10, 2)->default(0); // Para descuentos aplicados al pedido
            $table->decimal('total', 10, 2); // Subtotal + envío + impuestos - descuento
            
            // Estado y Pago
            $table->string('status')->default('pending'); // pending, processing, payment_failed, shipped, delivered, completed, cancelled
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway')->nullable(); // Ej: 'stripe', 'paymentez'
            $table->string('payment_gateway_transaction_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable(); // Fecha en que se confirmó el pago

            $table->text('notes')->nullable(); // Notas del cliente o administrativas
            
            $table->timestamps(); // created_at (fecha del pedido), updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
