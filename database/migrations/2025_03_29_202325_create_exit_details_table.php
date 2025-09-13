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
        Schema::create('exit_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exit_id')->nullable()->constrained('inventory_exits')->onDelete('set null'); 
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exit_details', function (Blueprint $table) {
            $table->dropForeign(['exit_id']);
            $table->dropForeign(['product_id']);       
        });  
    
        Schema::dropIfExists('exit_details');
    }
};
