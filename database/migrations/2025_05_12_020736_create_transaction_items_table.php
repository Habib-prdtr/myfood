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
        if (Schema::hasTable('foods') && Schema::hasTable('transactions')) {
            Schema::create('transaction_items', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('foods_id')->constrained('foods')->cascadeOnDelete();
                $table->integer('quantity');
                $table->integer('price');
                $table->integer('subtotal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
