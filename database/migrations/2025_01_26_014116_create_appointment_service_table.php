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
        Schema::create('appointment_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade'); // Relación con la tabla appointments
            $table->foreignId('service_id')->constrained()->onDelete('cascade'); // Relación con la tabla services
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('appointment_service', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['service_id']);

        });  
        Schema::dropIfExists('appointment_service');
    }
};
