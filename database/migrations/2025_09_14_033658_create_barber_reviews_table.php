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
        Schema::create('barber_reviews', function (Blueprint $table) {
            $table->id();
            // Llaves foráneas
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('barber_id')->constrained('barbers')->onDelete('cascade');
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // puntuación 1–5
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barber_reviews', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['barber_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('barber_reviews');
    }
};
