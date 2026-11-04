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
        Schema::create('meal_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('meal_plan_id')->nullable()->constrained('meal_plans')->nullOnDelete();

            $table->date('date');
            $table->enum('meal_time', ['breakfast', 'lunch', 'dinner', 'snack'])->nullable();

            $table->json('meals');
            $table->json('food_items')->nullable();

            $table->integer('calories')->nullable();
            $table->float('protein')->nullable();
            $table->float('carbs')->nullable();
            $table->float('fat')->nullable();
            $table->float('water_intake')->nullable();

            $table->unsignedTinyInteger('hunger_level')->nullable();
            $table->boolean('meal_skipped')->default(false);
            $table->string('skip_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_logs');
    }
};

