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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('category')->index();

            $table->integer('calories_per_100g')->nullable();
            $table->float('protein_per_100g')->nullable();
            $table->float('carbs_per_100g')->nullable();
            $table->float('fat_per_100g')->nullable();
            $table->float('fiber_per_100g')->nullable();
            $table->string('unit')->default('g');

            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};

