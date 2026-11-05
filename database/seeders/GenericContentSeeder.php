<?php

namespace Database\Seeders;

use App\Models\GenericContent;
use Illuminate\Database\Seeder;

class GenericContentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [];

        // 10 sample workouts (free tier)
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'title' => "Free Workout #{$i}: Full Body Starter",
                'description' => "A simple full-body routine you can do anywhere. Focus on form and consistency.",
                'category' => 'workout',
                'content_type' => 'article',
                'content_url' => "https://example.com/free-workout-{$i}",
                'thumbnail_url' => null,
                'difficulty' => $i <= 4 ? 'beginner' : ($i <= 8 ? 'intermediate' : 'advanced'),
                'duration' => 20 + ($i * 2),
                'views_count' => 0,
            ];
        }

        // 10 nutrition articles
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'title' => "Nutrition Basics #{$i}: Balanced Plates",
                'description' => "Practical nutrition tips for building balanced meals and staying consistent.",
                'category' => 'nutrition',
                'content_type' => 'article',
                'content_url' => "https://example.com/nutrition-article-{$i}",
                'thumbnail_url' => null,
                'difficulty' => $i <= 6 ? 'beginner' : 'intermediate',
                'duration' => 6 + $i,
                'views_count' => 0,
            ];
        }

        // 5 wellness tips
        for ($i = 1; $i <= 5; $i++) {
            $items[] = [
                'title' => "Wellness Tip #{$i}: Daily Habit Builder",
                'description' => "A short, actionable tip to improve sleep, stress, and daily wellness habits.",
                'category' => 'wellness',
                'content_type' => 'article',
                'content_url' => "https://example.com/wellness-tip-{$i}",
                'thumbnail_url' => null,
                'difficulty' => 'beginner',
                'duration' => 3 + $i,
                'views_count' => 0,
            ];
        }

        foreach ($items as $item) {
            GenericContent::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}

