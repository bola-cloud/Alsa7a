<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Question;

class CategoriesAndQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General users',
                'questions' => [
                    'What is your main goal?',
                ],
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Students and learners',
                'questions' => [
                    'What is your study level?',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Working professionals',
                'questions' => [
                    'What is your monthly income range?',
                ],
            ],
        ];

        foreach ($data as $item) {
            $cat = Category::create([
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['description'],
            ]);

            foreach ($item['questions'] as $q) {
                Question::create([
                    'category_id' => $cat->id,
                    'question' => $q,
                ]);
            }
        }
    }
}
