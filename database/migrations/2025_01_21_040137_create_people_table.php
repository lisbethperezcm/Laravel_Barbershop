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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); // Nombre
            $table->string('last_name');  // Apellido
            $table->string('phone_number')->nullable(); // Teléfono
            $table->string('address')->nullable(); // Dirección
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('updated_by')->nullable(); 
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
           
            

         
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
       
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['updated_by']);

        });        Schema::dropIfExists('people');
    }
};
