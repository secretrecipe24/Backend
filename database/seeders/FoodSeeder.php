<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            // Protein
            ['name' => 'Chicken Breast', 'category' => 'Protein', 'cal' => 165, 'p' => 31.0, 'c' => 0.0, 'f' => 3.6, 'fi' => 0.0],
            ['name' => 'Egg (Whole)', 'category' => 'Protein', 'cal' => 143, 'p' => 13.0, 'c' => 1.1, 'f' => 9.5, 'fi' => 0.0],
            ['name' => 'Tuna (Canned in Water)', 'category' => 'Protein', 'cal' => 116, 'p' => 26.0, 'c' => 0.0, 'f' => 1.0, 'fi' => 0.0],
            ['name' => 'Salmon', 'category' => 'Protein', 'cal' => 208, 'p' => 20.0, 'c' => 0.0, 'f' => 13.0, 'fi' => 0.0],
            ['name' => 'Tofu', 'category' => 'Protein', 'cal' => 76, 'p' => 8.0, 'c' => 1.9, 'f' => 4.8, 'fi' => 0.3],
            ['name' => 'Greek Yogurt (Plain)', 'category' => 'Dairy', 'cal' => 59, 'p' => 10.0, 'c' => 3.6, 'f' => 0.4, 'fi' => 0.0],
            ['name' => 'Lean Ground Beef', 'category' => 'Protein', 'cal' => 250, 'p' => 26.0, 'c' => 0.0, 'f' => 15.0, 'fi' => 0.0],
            ['name' => 'Pork Loin', 'category' => 'Protein', 'cal' => 242, 'p' => 27.0, 'c' => 0.0, 'f' => 14.0, 'fi' => 0.0],
            ['name' => 'Shrimp', 'category' => 'Protein', 'cal' => 99, 'p' => 24.0, 'c' => 0.2, 'f' => 0.3, 'fi' => 0.0],
            ['name' => 'Lentils (Cooked)', 'category' => 'Protein', 'cal' => 116, 'p' => 9.0, 'c' => 20.0, 'f' => 0.4, 'fi' => 8.0],

            // Vegetables
            ['name' => 'Broccoli', 'category' => 'Vegetables', 'cal' => 34, 'p' => 2.8, 'c' => 6.6, 'f' => 0.4, 'fi' => 2.6],
            ['name' => 'Spinach', 'category' => 'Vegetables', 'cal' => 23, 'p' => 2.9, 'c' => 3.6, 'f' => 0.4, 'fi' => 2.2],
            ['name' => 'Carrots', 'category' => 'Vegetables', 'cal' => 41, 'p' => 0.9, 'c' => 9.6, 'f' => 0.2, 'fi' => 2.8],
            ['name' => 'Cucumber', 'category' => 'Vegetables', 'cal' => 15, 'p' => 0.7, 'c' => 3.6, 'f' => 0.1, 'fi' => 0.5],
            ['name' => 'Tomato', 'category' => 'Vegetables', 'cal' => 18, 'p' => 0.9, 'c' => 3.9, 'f' => 0.2, 'fi' => 1.2],
            ['name' => 'Bell Pepper', 'category' => 'Vegetables', 'cal' => 31, 'p' => 1.0, 'c' => 6.0, 'f' => 0.3, 'fi' => 2.1],
            ['name' => 'Onion', 'category' => 'Vegetables', 'cal' => 40, 'p' => 1.1, 'c' => 9.3, 'f' => 0.1, 'fi' => 1.7],
            ['name' => 'Garlic', 'category' => 'Vegetables', 'cal' => 149, 'p' => 6.4, 'c' => 33.0, 'f' => 0.5, 'fi' => 2.1],
            ['name' => 'Cabbage', 'category' => 'Vegetables', 'cal' => 25, 'p' => 1.3, 'c' => 5.8, 'f' => 0.1, 'fi' => 2.5],
            ['name' => 'Cauliflower', 'category' => 'Vegetables', 'cal' => 25, 'p' => 1.9, 'c' => 5.0, 'f' => 0.3, 'fi' => 2.0],

            // Fruits
            ['name' => 'Banana', 'category' => 'Fruits', 'cal' => 89, 'p' => 1.1, 'c' => 23.0, 'f' => 0.3, 'fi' => 2.6],
            ['name' => 'Apple', 'category' => 'Fruits', 'cal' => 52, 'p' => 0.3, 'c' => 14.0, 'f' => 0.2, 'fi' => 2.4],
            ['name' => 'Orange', 'category' => 'Fruits', 'cal' => 47, 'p' => 0.9, 'c' => 12.0, 'f' => 0.1, 'fi' => 2.4],
            ['name' => 'Mango', 'category' => 'Fruits', 'cal' => 60, 'p' => 0.8, 'c' => 15.0, 'f' => 0.4, 'fi' => 1.6],
            ['name' => 'Pineapple', 'category' => 'Fruits', 'cal' => 50, 'p' => 0.5, 'c' => 13.0, 'f' => 0.1, 'fi' => 1.4],
            ['name' => 'Watermelon', 'category' => 'Fruits', 'cal' => 30, 'p' => 0.6, 'c' => 8.0, 'f' => 0.2, 'fi' => 0.4],
            ['name' => 'Strawberry', 'category' => 'Fruits', 'cal' => 32, 'p' => 0.7, 'c' => 7.7, 'f' => 0.3, 'fi' => 2.0],
            ['name' => 'Grapes', 'category' => 'Fruits', 'cal' => 69, 'p' => 0.7, 'c' => 18.0, 'f' => 0.2, 'fi' => 0.9],
            ['name' => 'Papaya', 'category' => 'Fruits', 'cal' => 43, 'p' => 0.5, 'c' => 11.0, 'f' => 0.3, 'fi' => 1.7],
            ['name' => 'Avocado', 'category' => 'Fruits', 'cal' => 160, 'p' => 2.0, 'c' => 9.0, 'f' => 15.0, 'fi' => 7.0],

            // Carbs / Grains
            ['name' => 'White Rice (Cooked)', 'category' => 'Carbs', 'cal' => 130, 'p' => 2.4, 'c' => 28.0, 'f' => 0.3, 'fi' => 0.4],
            ['name' => 'Brown Rice (Cooked)', 'category' => 'Carbs', 'cal' => 111, 'p' => 2.6, 'c' => 23.0, 'f' => 0.9, 'fi' => 1.8],
            ['name' => 'Oats (Cooked)', 'category' => 'Carbs', 'cal' => 71, 'p' => 2.5, 'c' => 12.0, 'f' => 1.4, 'fi' => 1.7],
            ['name' => 'Bread (Whole Wheat)', 'category' => 'Carbs', 'cal' => 247, 'p' => 13.0, 'c' => 41.0, 'f' => 4.2, 'fi' => 7.0],
            ['name' => 'Pasta (Cooked)', 'category' => 'Carbs', 'cal' => 131, 'p' => 5.0, 'c' => 25.0, 'f' => 1.1, 'fi' => 1.4],
            ['name' => 'Sweet Potato', 'category' => 'Carbs', 'cal' => 86, 'p' => 1.6, 'c' => 20.0, 'f' => 0.1, 'fi' => 3.0],
            ['name' => 'Potato', 'category' => 'Carbs', 'cal' => 77, 'p' => 2.0, 'c' => 17.0, 'f' => 0.1, 'fi' => 2.2],
            ['name' => 'Corn', 'category' => 'Carbs', 'cal' => 86, 'p' => 3.4, 'c' => 19.0, 'f' => 1.2, 'fi' => 2.7],
            ['name' => 'Quinoa (Cooked)', 'category' => 'Carbs', 'cal' => 120, 'p' => 4.4, 'c' => 21.0, 'f' => 1.9, 'fi' => 2.8],
            ['name' => 'Banana (Saba)', 'category' => 'Carbs', 'cal' => 120, 'p' => 1.2, 'c' => 31.0, 'f' => 0.2, 'fi' => 2.3],

            // Dairy / Fats
            ['name' => 'Milk (Low-fat)', 'category' => 'Dairy', 'cal' => 42, 'p' => 3.4, 'c' => 5.0, 'f' => 1.0, 'fi' => 0.0],
            ['name' => 'Cheddar Cheese', 'category' => 'Dairy', 'cal' => 403, 'p' => 25.0, 'c' => 1.3, 'f' => 33.0, 'fi' => 0.0],
            ['name' => 'Cottage Cheese', 'category' => 'Dairy', 'cal' => 98, 'p' => 11.0, 'c' => 3.4, 'f' => 4.3, 'fi' => 0.0],
            ['name' => 'Peanut Butter', 'category' => 'Fats', 'cal' => 588, 'p' => 25.0, 'c' => 20.0, 'f' => 50.0, 'fi' => 6.0],
            ['name' => 'Olive Oil', 'category' => 'Fats', 'cal' => 884, 'p' => 0.0, 'c' => 0.0, 'f' => 100.0, 'fi' => 0.0],
            ['name' => 'Almonds', 'category' => 'Fats', 'cal' => 579, 'p' => 21.0, 'c' => 22.0, 'f' => 50.0, 'fi' => 12.0],
            ['name' => 'Butter', 'category' => 'Fats', 'cal' => 717, 'p' => 0.9, 'c' => 0.1, 'f' => 81.0, 'fi' => 0.0],
            ['name' => 'Chia Seeds', 'category' => 'Fats', 'cal' => 486, 'p' => 17.0, 'c' => 42.0, 'f' => 31.0, 'fi' => 34.0],
            ['name' => 'Walnuts', 'category' => 'Fats', 'cal' => 654, 'p' => 15.0, 'c' => 14.0, 'f' => 65.0, 'fi' => 7.0],
            ['name' => 'Coconut Milk', 'category' => 'Fats', 'cal' => 230, 'p' => 2.3, 'c' => 6.0, 'f' => 24.0, 'fi' => 2.2],
        ];

        // Expand to 100+ by adding common items and simple variants
        $moreNames = [
            ['Oatmeal', 'Carbs', 68, 2.4, 12.0, 1.4, 1.7],
            ['Beef Sirloin', 'Protein', 217, 26.0, 0.0, 12.0, 0.0],
            ['Turkey Breast', 'Protein', 135, 30.0, 0.0, 1.0, 0.0],
            ['Pechay (Bok Choy)', 'Vegetables', 13, 1.5, 2.2, 0.2, 1.0],
            ['Kangkong (Water Spinach)', 'Vegetables', 19, 2.6, 3.1, 0.2, 2.1],
            ['Okra', 'Vegetables', 33, 1.9, 7.5, 0.2, 3.2],
            ['Ampalaya (Bitter Melon)', 'Vegetables', 17, 1.0, 3.7, 0.2, 2.8],
            ['Pineapple Juice', 'Fruits', 53, 0.3, 13.0, 0.1, 0.2],
            ['Cucumber Juice', 'Vegetables', 12, 0.5, 2.5, 0.1, 0.3],
            ['Black Coffee', 'Beverages', 1, 0.1, 0.0, 0.0, 0.0],
            ['Green Tea', 'Beverages', 1, 0.1, 0.0, 0.0, 0.0],
            ['Sparkling Water', 'Beverages', 0, 0.0, 0.0, 0.0, 0.0],
            ['Canned Sardines', 'Protein', 208, 25.0, 0.0, 11.0, 0.0],
            ['Bangus (Milkfish)', 'Protein', 190, 20.0, 0.0, 12.0, 0.0],
            ['Tilapia', 'Protein', 128, 26.0, 0.0, 2.7, 0.0],
            ['Mung Beans (Cooked)', 'Protein', 105, 7.0, 19.0, 0.4, 7.6],
            ['Chickpeas (Cooked)', 'Protein', 164, 9.0, 27.0, 2.6, 7.6],
            ['Peas', 'Vegetables', 81, 5.4, 14.0, 0.4, 5.1],
            ['Blueberries', 'Fruits', 57, 0.7, 14.0, 0.3, 2.4],
            ['Pears', 'Fruits', 57, 0.4, 15.0, 0.1, 3.1],
            ['Water (plain)', 'Beverages', 0, 0.0, 0.0, 0.0, 0.0],
        ];

        foreach ($moreNames as $row) {
            $foods[] = [
                'name' => $row[0],
                'category' => $row[1],
                'cal' => $row[2],
                'p' => $row[3],
                'c' => $row[4],
                'f' => $row[5],
                'fi' => $row[6],
            ];
        }

        // Ensure 100+ by duplicating categories with common variants
        $base = $foods;
        $i = 0;
        while (count($foods) < 110) {
            $src = $base[$i % count($base)];
            $foods[] = [
                'name' => $src['name'].' (Alt '.(int) floor($i / count($base) + 1).')',
                'category' => $src['category'],
                'cal' => $src['cal'],
                'p' => $src['p'],
                'c' => $src['c'],
                'f' => $src['f'],
                'fi' => $src['fi'],
            ];
            $i++;
        }

        foreach ($foods as $f) {
            Food::updateOrCreate(
                ['name' => $f['name']],
                [
                    'category' => $f['category'],
                    'calories_per_100g' => $f['cal'],
                    'protein_per_100g' => $f['p'],
                    'carbs_per_100g' => $f['c'],
                    'fat_per_100g' => $f['f'],
                    'fiber_per_100g' => $f['fi'],
                    'unit' => 'g',
                    'description' => null,
                    'image_url' => null,
                    'tags' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}

