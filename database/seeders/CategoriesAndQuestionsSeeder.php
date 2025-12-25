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
                'name_ar' => 'لاعب',
                'slug' => 'player', // kept for reference, mapped to image if needed or ignored
                'image' => 'images-demo/player_category.jpg',
                'is_service_provider' => false,
                'description' => 'Players (amateur/professional)',
                'description_ar' => 'اللاعبون (هواة/محترفون)',
                'questions' => [
                    ['en' => 'What is your age category? (Junior / Youth / Senior)', 'ar' => 'ما هي فئتك العمرية؟ (ناشئين / شباب / كبار)'],
                    ['en' => 'What is your primary position?', 'ar' => 'ما هو مركزك الأساسي؟'],
                    ['en' => 'What is your playing level? (Amateur / Academy / Experienced / Professional)', 'ar' => 'ما هو مستوى لعبك؟ (هواة / أكاديمي / ذو خبرة / محترف)'],
                    ['en' => 'What is your preferred foot (if applicable)?', 'ar' => 'ما هي قدمك المفضلة (إن أمكن)؟'],
                    ['en' => 'Do you have any previous injuries we should know about?', 'ar' => 'هل لديك أي إصابات سابقة يجب أن نعرف عنها؟'],
                ],
            ],
            [
                'name' => 'Coach',
                'name_ar' => 'مدرب',
                'slug' => 'coach',
                'image' => null,
                'is_service_provider' => true,
                'description' => 'Coaches and trainers',
                'description_ar' => 'المدربون والمدربات',
                'questions' => [
                    ['en' => 'What coaching licenses or certificates do you hold?', 'ar' => 'ما هي رخص أو شهادات التدريب التي تحملها؟'],
                    ['en' => 'How many years of coaching experience do you have?', 'ar' => 'كم سنة من الخبرة التدريبية لديك؟'],
                    ['en' => 'Which age groups do you coach most often?', 'ar' => 'أي الفئات العمرية تقوم بتدريبها غالبًا؟'],
                    ['en' => 'Are you available for private coaching sessions?', 'ar' => 'هل أنت متاح لجلسات تدريب خاصة؟'],
                ],
            ],
            [
                'name' => 'Club',
                'name_ar' => 'نادي',
                'slug' => 'club',
                'image' => null,
                'is_service_provider' => false,
                'description' => 'Clubs and academies',
                'description_ar' => 'الأندية والأكاديميات',
                'questions' => [
                    ['en' => 'What type of club are you? (Official / Community / Academy)', 'ar' => 'ما نوع النادي؟ (رسمي / مجتمعي / أكاديمي)'],
                    ['en' => 'How many teams does the club manage?', 'ar' => 'كم عدد الفرق التي يديرها النادي؟'],
                    ['en' => 'Which sports does the club operate in?', 'ar' => 'في أي رياضات يعمل النادي؟'],
                    ['en' => 'Do you accept player join requests?', 'ar' => 'هل تقبل طلبات انضمام لاعبين؟'],
                ],
            ],
            [
                'name' => 'Photographer',
                'name_ar' => 'مصور',
                'slug' => 'photographer',
                'image' => null,
                'is_service_provider' => true,
                'description' => 'Event photographers and videographers',
                'description_ar' => 'مصورو الفعاليات والفيديوغرافيون',
                'questions' => [
                    ['en' => 'What coverage types do you offer? (Photo / Video / Live broadcast)', 'ar' => 'ما أنواع التغطية التي تقدمها؟ (تصوير / فيديو / بث مباشر)'],
                    ['en' => 'Do you provide editing and post-production services?', 'ar' => 'هل تقدم خدمات التحرير وما بعد الإنتاج؟'],
                    ['en' => 'Do you have sample work or a portfolio link?', 'ar' => 'هل لديك أعمال سابقة أو رابط للمحفظة؟'],
                    ['en' => 'Are you available for event-based bookings?', 'ar' => 'هل أنت متاح للحجوزات حسب الفعاليات؟'],
                ],
            ],
            [
                'name' => 'Physiotherapist',
                'name_ar' => 'أخصائي علاج طبيعي',
                'slug' => 'physiotherapist',
                'image' => null,
                'is_service_provider' => true,
                'description' => 'Sports therapists and physios',
                'description_ar' => 'أخصائيو العلاج الطبيعي والفيزيوثيرابيين',
                'questions' => [
                    ['en' => 'What qualifications or certifications do you hold?', 'ar' => 'ما هي مؤهلاتك أو الشهادات التي تحملها؟'],
                    ['en' => 'Do you offer in-clinic and on-field services?', 'ar' => 'هل تقدم خدمات داخل العيادة وعلى أرض الملعب؟'],
                    ['en' => 'What treatment types do you specialise in?', 'ar' => 'ما أنواع العلاجات التي تتخصص بها؟'],
                    ['en' => 'Are you available for match-day support?', 'ar' => 'هل أنت متاح لدعم يوم المباراة؟'],
                ],
            ],
            [
                'name' => 'Parent',
                'name_ar' => 'ولي أمر',
                'slug' => 'parent',
                'image' => null,
                'is_service_provider' => false,
                'description' => 'Parents of players',
                'description_ar' => 'أولياء أمور اللاعبين',
                'questions' => [
                    ['en' => 'How many children are participating?', 'ar' => 'كم عدد الأطفال المشاركين؟'],
                    ['en' => 'What are the preferred sports for your children?', 'ar' => 'ما هي الرياضات المفضلة لأطفالك؟'],
                    ['en' => 'Would you like weekly progress reports?', 'ar' => 'هل ترغب في تقارير تقدم أسبوعية؟'],
                ],
            ],
            [
                'name' => 'Fan',
                'name_ar' => 'مشجع',
                'slug' => 'fan',
                'image' => null,
                'is_service_provider' => false,
                'description' => 'Fans and supporters',
                'description_ar' => 'المشجعون والمؤيدون',
                'questions' => [
                    ['en' => 'Which teams do you follow most closely?', 'ar' => 'أي الفرق تتابعها عن كثب؟'],
                    ['en' => 'Do you attend matches regularly?', 'ar' => 'هل تحضر المباريات بانتظام؟'],
                ],
            ],
            [
                'name' => 'Agent',
                'name_ar' => 'وكيل',
                'slug' => 'agent',
                'image' => null,
                'is_service_provider' => false,
                'description' => 'Player agents and scouts',
                'description_ar' => 'وكلاء اللاعبين والكشافون',
                'questions' => [
                    ['en' => 'Do you represent players currently?', 'ar' => 'هل تمثل لاعبين حاليًا؟'],
                    ['en' => 'Which player age groups do you scout?', 'ar' => 'أي فئات عمرية للاعبين تقوم بالتنقيب عنها؟'],
                    ['en' => 'What regions do you operate in?', 'ar' => 'في أي مناطق تعمل؟'],
                ],
            ],
        ];

        foreach ($data as $item) {
            // find existing category by name (en)
            $cat = Category::where('name_en', $item['name'])->first();
            if (!$cat) {
                // If not found, create matching SQL dump logic
                $cat = Category::create([
                    'name' => $item['name'],
                    'name_en' => $item['name'],
                    'name_ar' => $item['name_ar'] ?? $item['name'],
                    'image' => $item['image'] ?? null,
                    'is_service_provider' => $item['is_service_provider'] ?? false,
                    'description' => $item['description'] ?? null,
                    'description_en' => $item['description'] ?? null,
                    'description_ar' => $item['description_ar'] ?? null,
                ]);
            } else {
                // ensure localized fields and flags exist
                $cat->fill([
                    'name_ar' => $item['name_ar'] ?? $cat->name_ar ?? $item['name'],
                    'image' => $item['image'] ?? $cat->image,
                    'is_service_provider' => $item['is_service_provider'] ?? $cat->is_service_provider,
                    'description_en' => $item['description'] ?? $cat->description_en ?? $item['description'],
                    'description_ar' => $item['description_ar'] ?? $cat->description_ar ?? $item['description_ar'] ?? $item['description'],
                ]);
                $cat->save();
            }

            // Insert text questions from the dataset only if they don't already exist
            foreach ($item['questions'] as $q) {
                $exists = Question::where('category_id', $cat->id)
                    ->where('question_en', $q['en'] ?? null)
                    ->exists();
                if ($exists) {
                    continue;
                }

                Question::create([
                    'category_id' => $cat->id,
                    'question' => $q['en'] ?? null,
                    'question_en' => $q['en'] ?? null,
                    'question_ar' => $q['ar'] ?? null,
                    'type' => $q['type'] ?? 'text',
                    'choices' => $q['choices'] ?? null,
                ]);
            }

            // Add example questions of other types if they don't exist for this category
            // Types to ensure: multiple_choice, boolean, number

            // 1) multiple_choice example
            $hasMcq = Question::where('category_id', $cat->id)
                ->where('type', 'multiple_choice')
                ->exists();
            if (!$hasMcq) {
                Question::create([
                    'category_id' => $cat->id,
                    'question' => 'Which of the following best describes you?',
                    'question_en' => 'Which of the following best describes you?',
                    'question_ar' => 'أي مما يلي يصفك بشكل أفضل؟',
                    'type' => 'multiple_choice',
                    'choices' => [
                        'Option 1',
                        'Option 2',
                        'Option 3',
                    ],
                ]);
            }

            // 2) boolean example
            $hasBool = Question::where('category_id', $cat->id)
                ->where('type', 'boolean')
                ->exists();
            if (!$hasBool) {
                Question::create([
                    'category_id' => $cat->id,
                    'question' => 'Are you available for contact?',
                    'question_en' => 'Are you available for contact?',
                    'question_ar' => 'هل أنت متاح للتواصل؟',
                    'type' => 'boolean',
                    'choices' => null,
                ]);
            }

            // 3) number example
            $hasNumber = Question::where('category_id', $cat->id)
                ->where('type', 'number')
                ->exists();
            if (!$hasNumber) {
                Question::create([
                    'category_id' => $cat->id,
                    'question' => 'How many years of experience do you have? (enter a number)',
                    'question_en' => 'How many years of experience do you have? (enter a number)',
                    'question_ar' => 'كم سنة من الخبرة لديك؟ (أدخل رقما)',
                    'type' => 'number',
                    'choices' => null,
                ]);
            }
        }
    }
}
