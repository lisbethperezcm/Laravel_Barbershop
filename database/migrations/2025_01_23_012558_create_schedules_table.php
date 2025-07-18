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
        Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('barber_id')->constrained()->onDelete('cascade');
        $table->foreignId('day_id')->constrained()->onDelete('cascade');
        $table->time('start_time');
        $table->time('end_time');
        $table->foreignId('status_id')->nullable()->default(1)->constrained('statuses')->onDelete('set null'); // Status of the schedule
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
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['barber_id']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['status_id']);

        });
        Schema::dropIfExists('schedules');
    }
};
