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
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('status')->default('inactive'); // Usando string con valor por defecto 'inactive'
            $table->time('lunch_start')->nullable(); // Hora de inicio del almuerzo
            $table->time('lunch_end')->nullable(); // Hora de fin del almuerzo
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::table('barbers', function (Blueprint $table) {
        $table->dropForeign(['person_id']);  // Eliminar la clave foránea
    });
        Schema::dropIfExists('barbers');
    }
};
