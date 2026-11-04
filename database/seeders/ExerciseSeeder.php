<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // Chest
            ['name' => 'Push-Up', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'beginner', 'description' => 'Bodyweight push movement focusing on chest, triceps, and shoulders.', 'equipment' => null],
            ['name' => 'Incline Push-Up', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'beginner', 'description' => 'Push-up with hands elevated to reduce load.', 'equipment' => 'Bench'],
            ['name' => 'Decline Push-Up', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'intermediate', 'description' => 'Push-up with feet elevated to increase upper-chest emphasis.', 'equipment' => 'Bench'],
            ['name' => 'Bench Press', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'intermediate', 'description' => 'Barbell press from flat bench.', 'equipment' => 'Barbell'],
            ['name' => 'Dumbbell Bench Press', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'intermediate', 'description' => 'Dumbbell press from flat bench for greater range of motion.', 'equipment' => 'Dumbbells'],
            ['name' => 'Incline Bench Press', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'advanced', 'description' => 'Press on an incline bench targeting upper chest.', 'equipment' => 'Barbell'],
            ['name' => 'Chest Fly (Dumbbell)', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'intermediate', 'description' => 'Isolation movement opening and closing the arms to target pecs.', 'equipment' => 'Dumbbells'],
            ['name' => 'Cable Fly', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'advanced', 'description' => 'Cable fly variation providing constant tension.', 'equipment' => 'Cable Machine'],

            // Back
            ['name' => 'Pull-Up', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'advanced', 'description' => 'Vertical pull using bodyweight to target lats.', 'equipment' => 'Pull-up Bar'],
            ['name' => 'Assisted Pull-Up', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'beginner', 'description' => 'Pull-up assisted by band or machine.', 'equipment' => 'Band/Machine'],
            ['name' => 'Lat Pulldown', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'beginner', 'description' => 'Cable pulldown targeting lats.', 'equipment' => 'Cable Machine'],
            ['name' => 'Bent-Over Row (Barbell)', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'advanced', 'description' => 'Hip-hinged row targeting mid-back and lats.', 'equipment' => 'Barbell'],
            ['name' => 'One-Arm Dumbbell Row', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'intermediate', 'description' => 'Single-arm row for lats and rhomboids.', 'equipment' => 'Dumbbell'],
            ['name' => 'Seated Cable Row', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'beginner', 'description' => 'Cable row targeting mid-back.', 'equipment' => 'Cable Machine'],
            ['name' => 'Face Pull', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'intermediate', 'description' => 'Rear-delt and upper-back pull for posture.', 'equipment' => 'Cable Machine'],
            ['name' => 'Deadlift', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'advanced', 'description' => 'Full-body hip hinge emphasizing posterior chain.', 'equipment' => 'Barbell'],

            // Legs
            ['name' => 'Bodyweight Squat', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Foundational squat pattern for legs and hips.', 'equipment' => null],
            ['name' => 'Goblet Squat', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Squat holding a dumbbell/kettlebell at chest.', 'equipment' => 'Dumbbell/Kettlebell'],
            ['name' => 'Back Squat', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'advanced', 'description' => 'Barbell squat on the back.', 'equipment' => 'Barbell'],
            ['name' => 'Front Squat', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'advanced', 'description' => 'Barbell squat with front rack position.', 'equipment' => 'Barbell'],
            ['name' => 'Walking Lunge', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'intermediate', 'description' => 'Alternating lunge steps targeting quads and glutes.', 'equipment' => null],
            ['name' => 'Reverse Lunge', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Step-back lunge focusing on stability.', 'equipment' => null],
            ['name' => 'Romanian Deadlift', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'advanced', 'description' => 'Hip hinge targeting hamstrings and glutes.', 'equipment' => 'Barbell/Dumbbells'],
            ['name' => 'Leg Press', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Machine press for quads/glutes.', 'equipment' => 'Leg Press Machine'],
            ['name' => 'Calf Raise', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Targets calves; can be performed standing or seated.', 'equipment' => null],
            ['name' => 'Glute Bridge', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Hip extension targeting glutes.', 'equipment' => null],

            // Shoulders
            ['name' => 'Overhead Press', 'category' => 'Strength', 'muscle_group' => 'Shoulders', 'difficulty' => 'intermediate', 'description' => 'Vertical press targeting deltoids.', 'equipment' => 'Barbell/Dumbbells'],
            ['name' => 'Dumbbell Shoulder Press', 'category' => 'Strength', 'muscle_group' => 'Shoulders', 'difficulty' => 'beginner', 'description' => 'Dumbbell press variation for shoulders.', 'equipment' => 'Dumbbells'],
            ['name' => 'Lateral Raise', 'category' => 'Strength', 'muscle_group' => 'Shoulders', 'difficulty' => 'beginner', 'description' => 'Isolation for lateral delts.', 'equipment' => 'Dumbbells'],
            ['name' => 'Front Raise', 'category' => 'Strength', 'muscle_group' => 'Shoulders', 'difficulty' => 'beginner', 'description' => 'Isolation for anterior delts.', 'equipment' => 'Dumbbells'],
            ['name' => 'Rear Delt Fly', 'category' => 'Strength', 'muscle_group' => 'Shoulders', 'difficulty' => 'intermediate', 'description' => 'Targets posterior delts.', 'equipment' => 'Dumbbells'],
            ['name' => 'Arnold Press', 'category' => 'Strength', 'muscle_group' => 'Shoulders', 'difficulty' => 'advanced', 'description' => 'Rotational press for full delt development.', 'equipment' => 'Dumbbells'],

            // Arms
            ['name' => 'Bicep Curl', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'beginner', 'description' => 'Elbow flexion targeting biceps.', 'equipment' => 'Dumbbells'],
            ['name' => 'Hammer Curl', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'beginner', 'description' => 'Neutral-grip curl targeting brachialis.', 'equipment' => 'Dumbbells'],
            ['name' => 'Tricep Extension', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'beginner', 'description' => 'Overhead tricep extension.', 'equipment' => 'Dumbbell'],
            ['name' => 'Tricep Pushdown', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'beginner', 'description' => 'Cable pushdown targeting triceps.', 'equipment' => 'Cable Machine'],
            ['name' => 'Skull Crushers', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'intermediate', 'description' => 'Lying tricep extension.', 'equipment' => 'EZ Bar/Dumbbells'],
            ['name' => 'Close-Grip Bench Press', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'advanced', 'description' => 'Bench press variation emphasizing triceps.', 'equipment' => 'Barbell'],

            // Core
            ['name' => 'Plank', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'beginner', 'description' => 'Anti-extension hold for core stability.', 'equipment' => null],
            ['name' => 'Side Plank', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'intermediate', 'description' => 'Lateral core stability hold.', 'equipment' => null],
            ['name' => 'Crunch', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'beginner', 'description' => 'Abdominal flexion exercise.', 'equipment' => null],
            ['name' => 'Bicycle Crunch', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'intermediate', 'description' => 'Alternating elbow-to-knee ab movement.', 'equipment' => null],
            ['name' => 'Hanging Knee Raise', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'advanced', 'description' => 'Hip flexion + core; raises knees while hanging.', 'equipment' => 'Pull-up Bar'],
            ['name' => 'Russian Twist', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'intermediate', 'description' => 'Rotational core exercise.', 'equipment' => 'Dumbbell/Medicine Ball'],

            // Cardio / Conditioning (to exceed 50)
            ['name' => 'Jump Rope', 'category' => 'Cardio', 'muscle_group' => null, 'difficulty' => 'beginner', 'description' => 'Cardio conditioning using a rope.', 'equipment' => 'Jump Rope'],
            ['name' => 'Burpee', 'category' => 'Cardio', 'muscle_group' => null, 'difficulty' => 'advanced', 'description' => 'Full-body conditioning movement.', 'equipment' => null],
            ['name' => 'Mountain Climbers', 'category' => 'Cardio', 'muscle_group' => 'Core', 'difficulty' => 'beginner', 'description' => 'Fast plank knee drives for cardio + core.', 'equipment' => null],
            ['name' => 'Rowing (Machine)', 'category' => 'Cardio', 'muscle_group' => 'Back', 'difficulty' => 'beginner', 'description' => 'Low-impact full-body cardio.', 'equipment' => 'Rowing Machine'],
            ['name' => 'Cycling (Stationary)', 'category' => 'Cardio', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Cardio training on a stationary bike.', 'equipment' => 'Bike'],
            ['name' => 'Treadmill Walk', 'category' => 'Cardio', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Steady-state walk on treadmill.', 'equipment' => 'Treadmill'],
            ['name' => 'Treadmill Run', 'category' => 'Cardio', 'muscle_group' => 'Legs', 'difficulty' => 'intermediate', 'description' => 'Steady-state run on treadmill.', 'equipment' => 'Treadmill'],
            ['name' => 'HIIT Intervals', 'category' => 'Cardio', 'muscle_group' => null, 'difficulty' => 'advanced', 'description' => 'High intensity intervals for conditioning.', 'equipment' => null],
        ];

        // Add a few more variants to ensure 50+
        $variants = [
            ['name' => 'Incline Dumbbell Fly', 'category' => 'Strength', 'muscle_group' => 'Chest', 'difficulty' => 'advanced', 'description' => 'Upper chest isolation fly on incline.', 'equipment' => 'Dumbbells'],
            ['name' => 'Chin-Up', 'category' => 'Strength', 'muscle_group' => 'Back', 'difficulty' => 'advanced', 'description' => 'Supinated pull-up emphasizing biceps and lats.', 'equipment' => 'Pull-up Bar'],
            ['name' => 'Step-Up', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'beginner', 'description' => 'Single-leg step onto elevated surface.', 'equipment' => 'Bench'],
            ['name' => 'Hip Thrust', 'category' => 'Strength', 'muscle_group' => 'Legs', 'difficulty' => 'intermediate', 'description' => 'Loaded glute bridge variation.', 'equipment' => 'Barbell/Bench'],
            ['name' => 'Farmer Carry', 'category' => 'Strength', 'muscle_group' => 'Arms', 'difficulty' => 'intermediate', 'description' => 'Loaded carry for grip and core stability.', 'equipment' => 'Dumbbells/Kettlebells'],
            ['name' => 'Pallof Press', 'category' => 'Core', 'muscle_group' => 'Core', 'difficulty' => 'intermediate', 'description' => 'Anti-rotation press for core.', 'equipment' => 'Cable/Band'],
        ];

        $exercises = array_merge($exercises, $variants);

        foreach ($exercises as $e) {
            Exercise::updateOrCreate(
                ['name' => $e['name']],
                [
                    'category' => $e['category'],
                    'muscle_group' => $e['muscle_group'],
                    'description' => $e['description'],
                    'instructions' => null,
                    'difficulty' => $e['difficulty'],
                    'equipment' => $e['equipment'],
                    'video_url' => null,
                    'image_url' => null,
                    'tags' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}

