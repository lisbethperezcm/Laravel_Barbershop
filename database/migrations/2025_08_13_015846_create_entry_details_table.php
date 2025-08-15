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
      Schema::create('entry_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->nullable()->constrained('inventory_entries')->onDelete('set null'); 
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entry_details', function (Blueprint $table) {
            $table->dropForeign(['entry_id']);
            $table->dropForeign(['product_id']);       
        });  

        Schema::dropIfExists('entry_details');
    }
};
