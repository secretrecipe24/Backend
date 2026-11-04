<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->float('height')->nullable()->change();
            $table->float('bmi')->nullable()->change();
            $table->float('body_fat_percentage')->nullable()->change();
            $table->float('chest')->nullable()->change();
            $table->float('waist')->nullable()->change();
            $table->float('hips')->nullable()->change();
            $table->float('thighs')->nullable()->change();
            $table->float('arms')->nullable()->change();
            $table->float('forearms')->nullable()->change();
            $table->float('calves')->nullable()->change();
            $table->float('neck')->nullable()->change();
            $table->float('shoulders')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->float('height')->nullable(false)->change();
            $table->float('bmi')->nullable(false)->change();
            $table->float('body_fat_percentage')->nullable(false)->change();
            $table->float('chest')->nullable(false)->change();
            $table->float('waist')->nullable(false)->change();
            $table->float('hips')->nullable(false)->change();
            $table->float('thighs')->nullable(false)->change();
            $table->float('arms')->nullable(false)->change();
            $table->float('forearms')->nullable(false)->change();
            $table->float('calves')->nullable(false)->change();
            $table->float('neck')->nullable(false)->change();
            $table->float('shoulders')->nullable(false)->change();
        });
    }
};
