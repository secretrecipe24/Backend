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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained('coaches')->cascadeOnDelete();

            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->float('match_score')->nullable();
            $table->boolean('selected_by_user')->default(false);

            $table->timestamps();

            $table->unique(['client_id', 'status']);
            $table->index(['coach_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

