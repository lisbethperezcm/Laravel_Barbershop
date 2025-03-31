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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            //Relacion de la tabla clients con la table people
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        //Elimina la clave foranea antes de eliminar la tabla 

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['person_id']);  // Eliminar la clave foránea
        });

    
        Schema::dropIfExists('clients');
    }
};
