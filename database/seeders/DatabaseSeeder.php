<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            ExerciseSeeder::class,
            FoodSeeder::class,
            GenericContentSeeder::class,
            CoachSeeder::class,
        ]);

        // User::factory(10)->create();
    }
}
