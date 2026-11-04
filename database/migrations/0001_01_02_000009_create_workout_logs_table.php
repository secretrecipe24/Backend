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
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->nullable()->constrained('workout_plans')->nullOnDelete();

            $table->date('date');
            $table->json('exercises');
            $table->json('performance')->nullable();

            $table->integer('duration')->nullable();
            $table->integer('calories_burned')->nullable();
            $table->integer('heart_rate_avg')->nullable();

            $table->enum('mood', ['great', 'good', 'okay', 'tired', 'exhausted'])->nullable();
            $table->unsignedTinyInteger('fatigue_level')->nullable();
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
        Schema::dropIfExists('workout_logs');
    }
};

