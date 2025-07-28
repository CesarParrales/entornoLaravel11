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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Guardar una referencia al producto, pero permitir que el producto sea eliminado sin afectar el ítem del pedido.
            // Los detalles del producto se almacenan directamente en esta tabla para mantener un registro histórico.
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null'); 
            
            $table->string('product_sku')->nullable(); // SKU en el momento de la compra
            $table->string('product_name'); // Nombre del producto en el momento de la compra
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Precio unitario en el momento de la compra
            $table->decimal('subtotal', 10, 2); // price * quantity
            
            $table->json('options')->nullable(); // Para configuración de bundles, variantes seleccionadas, etc.
                                               // Ejemplo: {'color': 'Rojo', 'talla': 'M'} o detalles de bundle
            
            // $table->integer('points_awarded')->default(0); // Si los puntos se calculan y almacenan por ítem
            // $table->decimal('discount_per_item', 10, 2)->default(0); // Si se aplican descuentos a nivel de ítem

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
