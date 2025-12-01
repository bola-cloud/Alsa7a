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
                'slug' => 'player',
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
            $cat = Category::create([
                'name' => $item['name'],
                'name_en' => $item['name'],
                'name_ar' => $item['name_ar'] ?? $item['name'],
                'image' => $item['image'] ?? null,
                'description' => $item['description'] ?? null,
                'description_en' => $item['description'] ?? null,
                'description_ar' => $item['description_ar'] ?? null,
            ]);

            foreach ($item['questions'] as $q) {
                Question::create([
                    'category_id' => $cat->id,
                    'question' => $q['en'] ?? null,
                    'question_en' => $q['en'] ?? null,
                    'question_ar' => $q['ar'] ?? null,
                    'type' => $q['type'] ?? 'text',
                    'choices' => $q['choices'] ?? null,
                ]);
            }
        }
    }
}
