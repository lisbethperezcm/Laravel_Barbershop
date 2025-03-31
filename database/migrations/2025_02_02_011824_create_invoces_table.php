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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->decimal('itbis', 10, 2);
            $table->foreignId('payment_type_id')->nullable()->constrained('payment_types')->onDelete('set null');
            $table->string('reference_number',50);//Numero de referencia si el pago se hace con transferencia  
            $table->string('aprovation_number',50);//Numero de aprobacion si el pago se hace con tarjeta  
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Relación con el usuario que creó la factura
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Relación con el usuario que actualizó la factura         
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['client_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['status_id']);
            $table->dropForeign(['payment_type_id']);

            
        });  
        Schema::dropIfExists('invoices');
    }
};
