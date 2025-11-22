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
                'name' => 'Player',
                'slug' => 'player',
                'description' => 'Players (amateur/professional)',
                'questions' => [
                    'What is your age category? (Junior / Youth / Senior)',
                    'What is your primary position?',
                    'What is your playing level? (Amateur / Academy / Experienced / Professional)',
                    'What is your preferred foot (if applicable)?',
                    'Do you have any previous injuries we should know about?',
                ],
            ],
            [
                'name' => 'Coach',
                'slug' => 'coach',
                'description' => 'Coaches and trainers',
                'questions' => [
                    'What coaching licenses or certificates do you hold?',
                    'How many years of coaching experience do you have?',
                    'Which age groups do you coach most often?',
                    'Are you available for private coaching sessions?',
                ],
            ],
            [
                'name' => 'Club',
                'slug' => 'club',
                'description' => 'Clubs and academies',
                'questions' => [
                    'What type of club are you? (Official / Community / Academy)',
                    'How many teams does the club manage?',
                    'Which sports does the club operate in?',
                    'Do you accept player join requests?',
                ],
            ],
            [
                'name' => 'Photographer',
                'slug' => 'photographer',
                'description' => 'Event photographers and videographers',
                'questions' => [
                    'What coverage types do you offer? (Photo / Video / Live broadcast)',
                    'Do you provide editing and post-production services?',
                    'Do you have sample work or a portfolio link?',
                    'Are you available for event-based bookings?',
                ],
            ],
            [
                'name' => 'Physiotherapist',
                'slug' => 'physiotherapist',
                'description' => 'Sports therapists and physios',
                'questions' => [
                    'What qualifications or certifications do you hold?',
                    'Do you offer in-clinic and on-field services?',
                    'What treatment types do you specialise in?',
                    'Are you available for match-day support?',
                ],
            ],
            [
                'name' => 'Parent',
                'slug' => 'parent',
                'description' => 'Parents of players',
                'questions' => [
                    'How many children are participating?',
                    'What are the preferred sports for your children?',
                    'Would you like weekly progress reports?',
                ],
            ],
            [
                'name' => 'Fan',
                'slug' => 'fan',
                'description' => 'Fans and supporters',
                'questions' => [
                    'Which teams do you follow most closely?',
                    'Do you attend matches regularly?',
                ],
            ],
            [
                'name' => 'Agent',
                'slug' => 'agent',
                'description' => 'Player agents and scouts',
                'questions' => [
                    'Do you represent players currently?',
                    'Which player age groups do you scout?',
                    'What regions do you operate in?',
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
