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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->json('fitness_goals')->nullable();
            $table->enum('coaching_style', ['motivational', 'strict', 'educational', 'supportive', 'no_preference'])->default('no_preference');

            $table->json('preferred_specialties')->nullable();
            $table->enum('communication_frequency', ['daily', 'few_times_week', 'weekly', 'bi_weekly', 'as_needed'])->default('as_needed');
            $table->enum('communication_style', ['chat_only', 'chat_with_checkins', 'video_calls', 'mixed'])->default('chat_only');

            $table->json('available_days')->nullable();
            $table->time('preferred_time_start')->nullable();
            $table->time('preferred_time_end')->nullable();

            $table->enum('experience_level', ['beginner', 'intermediate', 'advanced', 'any'])->default('any');
            $table->text('additional_notes')->nullable();
            $table->boolean('is_completed')->default(false);

            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

