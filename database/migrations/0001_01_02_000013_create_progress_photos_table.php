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
        Schema::create('progress_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('coaches')->nullOnDelete();

            $table->string('photo_path');
            $table->enum('photo_type', ['front', 'side', 'back']);

            $table->date('photo_date');
            $table->float('weight')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_private')->default(true);
            $table->boolean('is_approved')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'photo_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_photos');
    }
};

