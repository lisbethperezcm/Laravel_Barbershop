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
        
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); // Relación con la factura
            $table->foreignId('service_id')->constrained()->onDelete('cascade'); // Relación con el servicio
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['service_id']);
          

        });  
        Schema::dropIfExists('invoice_details');
    }
};
