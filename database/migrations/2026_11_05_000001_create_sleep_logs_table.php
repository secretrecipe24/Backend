<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sleep_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('bedtime')->nullable();
            $table->time('wake_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedTinyInteger('quality');
            $table->unsignedTinyInteger('interruptions')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'date']);
            $table->index(['client_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sleep_logs');
    }
};
