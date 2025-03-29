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
        Schema::create('inventory_exits', function (Blueprint $table) {
            $table->id();
            $table->string('exit_type');
            $table->date('exit_date');
            $table->text('note')->nullable(); 
            $table->decimal('total', 10, 2); 
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

        Schema::table('inventory_exits', function (Blueprint $table) {
         $table->dropForeign(['created_by']);
        $table->dropForeign(['updated_by']);

    });  
    Schema::dropIfExists('inventory_exits');
    }
};
