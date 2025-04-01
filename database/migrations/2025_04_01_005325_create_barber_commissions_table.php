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
        Schema::create('barber_commissions', function (Blueprint $table) {
            $table->id();
        $table->foreignId('barber_id')->constrained('barbers')->onDelete('cascade');
        $table->unsignedTinyInteger('current_percentage'); // Ejemplo: 30 = 30%
        $table->unsignedTinyInteger('previous_percentage')->nullable(); // Puede ser NULL
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     

        Schema::table('barber_commissions', function (Blueprint $table) {
            $table->dropForeign(['barber_id']);         
        });  

        Schema::dropIfExists('barber_commissions');
    }
};
