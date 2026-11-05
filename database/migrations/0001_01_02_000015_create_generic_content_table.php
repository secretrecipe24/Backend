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
        Schema::create('generic_content', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('category', ['workout', 'nutrition', 'wellness'])->index();
            $table->enum('content_type', ['article', 'video', 'image'])->index();

            $table->string('content_url');
            $table->string('thumbnail_url')->nullable();

            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->nullable()->index();

            $table->integer('duration')->nullable();
            $table->integer('views_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_content');
    }
};

