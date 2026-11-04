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
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            $table->text('certifications')->nullable();
            $table->json('expertise')->nullable();
            $table->json('specialties')->nullable();
            $table->text('bio')->nullable();
            $table->json('availability')->nullable();

            $table->float('rating')->default(0)->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();

            $table->foreignId('created_by')->constrained('admins')->cascadeOnDelete();

            $table->json('expertise_areas')->nullable();
            $table->json('coaching_styles')->nullable();
            $table->integer('years_experience')->default(0);
            $table->json('languages')->nullable();
            $table->json('client_demographics')->nullable();

            $table->decimal('monthly_rate', 10, 2)->nullable();
            $table->integer('max_clients')->default(10);
            $table->integer('current_clients')->default(0);

            $table->boolean('profile_complete')->default(false);
            $table->string('profile_photo')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};

