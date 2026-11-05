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
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('measurement_date');

            $table->float('weight');
            $table->float('height');
            $table->float('bmi');
            $table->float('body_fat_percentage');

            $table->float('chest');
            $table->float('waist');
            $table->float('hips');
            $table->float('thighs');

            $table->float('arms');
            $table->float('forearms');
            $table->float('calves');
            $table->float('neck');
            $table->float('shoulders');

            $table->json('strength_records')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'measurement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};

