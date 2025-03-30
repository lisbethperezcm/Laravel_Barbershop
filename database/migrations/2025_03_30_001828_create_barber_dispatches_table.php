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
        Schema::create('barber_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exit_id')->nullable()->constrained('inventory_exits')->onDelete('set null');
            $table->foreignId('barber_id')->nullable()->constrained('barbers')->onDelete('set null');
            $table->date('dispatch_date');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {


        Schema::table('barber_dispatches', function (Blueprint $table) {
            $table->dropForeign(['exit_id']);
            $table->dropForeign(['barber_id']);
            $table->dropForeign(['status_id']);  
            $table->dropForeign(['created_by']);  
            $table->dropForeign(['updated_by']);          
        });  
        Schema::dropIfExists('barber_dispatches');
    }
};
