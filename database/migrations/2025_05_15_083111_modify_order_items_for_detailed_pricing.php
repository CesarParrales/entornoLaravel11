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
        Schema::table('order_items', function (Blueprint $table) {
            // Renombrar columnas existentes
            $table->renameColumn('price', 'unit_price_before_vat');
            $table->renameColumn('subtotal', 'item_subtotal_before_vat');

            // Añadir nuevas columnas después de las renombradas (o en la posición deseada)
            // Para controlar el orden, se pueden añadir después de una columna específica: ->after('column_name')
            $table->decimal('item_vat_amount', 10, 2)->default(0)->after('item_subtotal_before_vat')->comment('Total VAT for this item line');
            $table->decimal('item_grand_total', 10, 2)->default(0)->after('item_vat_amount')->comment('Total price for this item line including VAT');
            $table->integer('points_value_at_purchase')->default(0)->after('product_name')->comment('Points value of the product at the time of purchase');
        });

        // Actualizar comentarios de las columnas renombradas si es necesario (requiere doctrine/dbal)
        // DB::statement("COMMENT ON COLUMN order_items.unit_price_before_vat IS 'Unit price at the time of purchase, before VAT'");
        // DB::statement("COMMENT ON COLUMN order_items.item_subtotal_before_vat IS 'Total price for this item line (quantity * unit_price_before_vat), before VAT'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Renombrar columnas a su estado original
            $table->renameColumn('unit_price_before_vat', 'price');
            $table->renameColumn('item_subtotal_before_vat', 'subtotal');

            // Eliminar las columnas añadidas
            $table->dropColumn(['item_vat_amount', 'item_grand_total', 'points_value_at_purchase']);
        });
    }
};
