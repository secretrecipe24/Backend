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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('password');
            $table->enum('gender', ['male', 'female', 'other', 'prefer-not-to-say'])->nullable()->after('age');

            $table->json('fitness_goals')->nullable()->after('gender');
            $table->json('dietary_preferences')->nullable()->after('fitness_goals');
            $table->text('restrictions')->nullable()->after('dietary_preferences');

            $table->enum('subscription_tier', ['free', 'premium'])->default('free')->after('restrictions')->index();
            $table->enum('subscription_status', ['active', 'inactive', 'expired'])->default('inactive')->after('subscription_tier')->index();
            $table->timestamp('subscription_expiry')->nullable()->after('subscription_status');

            $table->boolean('dark_mode')->default(true)->after('subscription_expiry');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['subscription_tier']);
            $table->dropIndex(['subscription_status']);
            $table->dropColumn([
                'age',
                'gender',
                'fitness_goals',
                'dietary_preferences',
                'restrictions',
                'subscription_tier',
                'subscription_status',
                'subscription_expiry',
                'dark_mode',
            ]);
        });
    }
};

