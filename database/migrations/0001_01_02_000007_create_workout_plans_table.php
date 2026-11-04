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
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coach_id')->constrained('coaches')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('matches')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->json('exercises');
            $table->enum('plan_type', ['daily', 'weekly', 'monthly']);
            $table->json('schedule')->nullable();

            $table->integer('duration')->nullable();
            $table->string('frequency')->nullable();
            $table->text('notes')->nullable();

            $table->json('goals')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft')->index();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['coach_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};

