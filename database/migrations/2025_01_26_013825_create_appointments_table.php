<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); // ID de la cita
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Relación con el cliente (cliente que reserva la cita)
            $table->foreignId('barber_id')->constrained('barbers')->onDelete('cascade'); // Relación con el barbero
            $table->date('appointment_date'); // Fecha
            $table->time('start_time');  // Hora de inicio
            $table->time('end_time');    // Hora de fin
             // Permitir valores NULL para `status_id`
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            
            $table->foreignId('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps(); // Campos created_at y updated_at
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['barber_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['status_id']);

        });  
        Schema::dropIfExists('appointments');
    }
};
