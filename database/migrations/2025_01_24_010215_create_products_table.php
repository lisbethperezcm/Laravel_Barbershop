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
            $table->string('name',100); // Nombre del producto
            $table->text('description')->nullable(); // Descripción del producto
            $table->decimal('sale_price', 10, 2); // Precio de venta
            $table->decimal('unit_cost', 10, 2); // Costo por unidad
            $table->integer('stock'); // Cantidad en stock
            $table->decimal('itbis', 10, 2); // Impuesto (ITBIS) en porcentaje
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

           
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

        });  
        Schema::dropIfExists('products');
    }
};
