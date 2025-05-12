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
        if (!Schema::hasTable('foods') && Schema::hasTable(table: 'categories')) {
            Schema::create('foods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->string('image');
                $table->integer('price');
                $table->string('price_afterdiscount')->nullable();
                $table->string('percent')->nullable();
                $table->boolean('is_promo')->nullable();
                $table->foreignId('categories_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
