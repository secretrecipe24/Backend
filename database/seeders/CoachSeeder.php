<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Coach;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = Admin::query()->where('email', 'admin@wellness.com')->value('id');

        $coaches = [
            [
                'name' => 'Coach Ava Santos',
                'email' => 'ava.coach@wellness.com',
                'password' => 'password',
                'certifications' => 'NASM-CPT, Nutrition Specialist',
                'expertise' => ['weight_loss', 'nutrition', 'cardio'],
                'specialties' => ['nutrition', 'weight_loss', 'meal_planning'],
                'bio' => 'Supportive coach focused on sustainable weight loss and nutrition habits.',
                'availability' => ['monday' => true, 'wednesday' => true, 'friday' => true],
                'coaching_styles' => ['supportive', 'educational'],
                'years_experience' => 5,
                'languages' => ['English', 'Tagalog'],
                'client_demographics' => ['beginner', 'intermediate'],
                'monthly_rate' => 1499,
            ],
            [
                'name' => 'Coach Miguel Reyes',
                'email' => 'miguel.coach@wellness.com',
                'password' => 'password',
                'certifications' => 'Strength & Conditioning Coach',
                'expertise' => ['strength_training', 'muscle_gain'],
                'specialties' => ['strength_training', 'form_correction'],
                'bio' => 'Strict but effective—build strength with solid fundamentals and progressive overload.',
                'availability' => ['tuesday' => true, 'thursday' => true, 'saturday' => true],
                'coaching_styles' => ['strict', 'motivational'],
                'years_experience' => 8,
                'languages' => ['English', 'Cebuano'],
                'client_demographics' => ['intermediate', 'advanced', 'athletes'],
                'monthly_rate' => 1999,
            ],
            [
                'name' => 'Coach Lea Dizon',
                'email' => 'lea.coach@wellness.com',
                'password' => 'password',
                'certifications' => 'Yoga & Mobility Instructor',
                'expertise' => ['flexibility', 'endurance'],
                'specialties' => ['flexibility', 'endurance'],
                'bio' => 'Educational mobility coach helping clients move better, feel better, and stay consistent.',
                'availability' => ['monday' => true, 'tuesday' => true, 'thursday' => true],
                'coaching_styles' => ['educational', 'supportive'],
                'years_experience' => 6,
                'languages' => ['English', 'Tagalog'],
                'client_demographics' => ['beginner', 'seniors'],
                'monthly_rate' => 1299,
            ],
            [
                'name' => 'Coach James Cruz',
                'email' => 'james.coach@wellness.com',
                'password' => 'password',
                'certifications' => 'Sports Performance Coach',
                'expertise' => ['sports_performance', 'cardio', 'strength_training'],
                'specialties' => ['endurance', 'form_correction', 'strength_training'],
                'bio' => 'Motivational coach for athletes—performance, recovery, and consistency.',
                'availability' => ['wednesday' => true, 'friday' => true, 'sunday' => true],
                'coaching_styles' => ['motivational'],
                'years_experience' => 10,
                'languages' => ['English', 'Tagalog'],
                'client_demographics' => ['athletes', 'advanced'],
                'monthly_rate' => 2499,
            ],
            [
                'name' => 'Coach Karen Lim',
                'email' => 'karen.coach@wellness.com',
                'password' => 'password',
                'certifications' => 'Registered Nutrition Coach',
                'expertise' => ['nutrition', 'weight_loss', 'general_fitness'],
                'specialties' => ['nutrition', 'meal_planning', 'weight_loss'],
                'bio' => 'Supportive nutrition-first approach for busy professionals.',
                'availability' => ['monday' => true, 'thursday' => true, 'saturday' => true],
                'coaching_styles' => ['supportive', 'educational'],
                'years_experience' => 4,
                'languages' => ['English', 'Tagalog'],
                'client_demographics' => ['beginner', 'intermediate'],
                'monthly_rate' => 1599,
            ],
        ];

        foreach ($coaches as $c) {
            Coach::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'password' => Hash::make($c['password']),
                    'certifications' => $c['certifications'],
                    'expertise' => $c['expertise'],
                    'specialties' => $c['specialties'],
                    'bio' => $c['bio'],
                    'availability' => $c['availability'],
                    'rating' => 0,
                    'status' => 'active',
                    'created_by' => $adminId,
                    'expertise_areas' => $c['expertise'],
                    'coaching_styles' => $c['coaching_styles'],
                    'years_experience' => $c['years_experience'],
                    'languages' => $c['languages'],
                    'client_demographics' => $c['client_demographics'],
                    'monthly_rate' => $c['monthly_rate'],
                    'max_clients' => 10,
                    'current_clients' => 0,
                    'profile_complete' => true,
                    'profile_photo' => null,
                ]
            );
        }
    }
}

