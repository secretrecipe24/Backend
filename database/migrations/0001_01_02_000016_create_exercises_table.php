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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('category')->index();
            $table->string('muscle_group')->nullable()->index();

            $table->text('description')->nullable();
            $table->text('instructions')->nullable();

            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->index();
            $table->string('equipment')->nullable();
            $table->string('video_url')->nullable();
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
        Schema::dropIfExists('exercises');
    }
};

