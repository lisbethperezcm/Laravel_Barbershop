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
        Schema::create('care_tips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

         Schema::table('care_tips', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });

            // Eliminar la tabla care_tips
            // Si no se elimina, puede causar problemas al intentar crearla de nuevo
            // en futuras migraciones.
            // Esto es especialmente importante si se usa el comando `migrate:fresh`
            // que elimina todas las tablas y las vuelve a crear.

        Schema::dropIfExists('care_tips');
    }
};
