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
        Schema::create('inventory_entries', function (Blueprint $table) {
            $table->id();
            $table->string('supplier')->nullable(); // Proveedor de la entrada
            $table->string('entry_type')->default('Compra'); // Tipo de entrada: Compra, Devolución, Ajuste, etc.
            $table->date('entry_date'); // Fecha de la entrada
            $table->string('invoice_number')->nullable(); // Número de la factura
            $table->string('note')->nullable(); // Nota u observaciones
            $table->decimal('total', 10, 2)->default(0); // Total calculado
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); 
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {

        Schema::table('inventory_entries', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('inventory_entries');
    }
};
