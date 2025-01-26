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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // Nombre único del rol
            $table->string('description')->nullable();  // Descripción del rol
            $table->timestamps();  // Crea las columnas created_at y updated_at automáticamente

            // Relación con la tabla users
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la clave foránea antes de eliminar la tabla
       /* Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);  // Eliminar la clave foránea
        });*/

        Schema::dropIfExists('roles');  // Eliminar la tabla roles
    }
};
