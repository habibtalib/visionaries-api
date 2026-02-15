<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $userId = Str::uuid()->toString();
        $now = Carbon::now();

        // 1. Create test user
        DB::table('users')->updateOrInsert(
            ['email' => 'test@visionaries.pro'],
            [
                'id' => $userId,
                'email' => 'test@visionaries.pro',
                'password' => Hash::make('password'),
                'display_name' => 'Ahmad Visionary',
                'language' => 'ms',
                'onboarding_completed' => true,
                'auth_provider' => 'email',
                'email_verified' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Re-fetch the user ID in case it already existed
        $userId = DB::table('users')->where('email', 'test@visionaries.pro')->value('id');

        // Clean up existing data for this user
        DB::table('visions')->where('user_id', $userId)->delete();
        DB::table('actions')->where('user_id', $userId)->delete();
        DB::table('check_ins')->where('user_id', $userId)->delete();
        DB::table('action_check_ins')->where('user_id', $userId)->delete();
        DB::table('journal_entries')->where('user_id', $userId)->delete();
        DB::table('user_traits')->where('user_id', $userId)->delete();
        DB::table('timeline_events')->where('user_id', $userId)->delete();
        DB::table('reviews')->where('user_id', $userId)->delete();

        // 2. Create vision
        $visionId = Str::uuid()->toString();
        $visionStatement = "To be a source of light and benefit for my family and community, leaving behind knowledge that continues to inspire long after I'm gone.";
        DB::table('visions')->insert([
            'id' => $visionId,
            'user_id' => $userId,
            'akhirah_intention' => 'To meet Allah with a heart that is content and grateful',
            'future_world' => 'A world where Muslim families are empowered with knowledge and purpose',
            'legacy' => 'Knowledge and values that continue to inspire generations',
            'generated_statement' => $visionStatement,
            'be_statement' => $visionStatement,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Create actions across all domains
        $actions = [
            ['title' => 'Morning dhikr after Fajr', 'domain' => 'spiritual', 'frequency' => 'daily', 'alignment' => 'Deepening connection with the Divine through daily remembrance'],
            ['title' => 'Read 10 pages of beneficial knowledge', 'domain' => 'knowledge', 'frequency' => 'daily', 'alignment' => 'Continuous self-improvement through curated knowledge'],
            ['title' => 'Quality time with family after Maghrib', 'domain' => 'family', 'frequency' => 'daily', 'alignment' => 'Strengthening family bonds through presence and love'],
            ['title' => 'Take a 30-minute walk in nature', 'domain' => 'health', 'frequency' => 'daily', 'alignment' => 'A sound body supports a sound mind and spirit'],
            ['title' => 'Weekly tahajjud practice', 'domain' => 'spiritual', 'frequency' => 'weekly', 'alignment' => 'The closest a servant is to Allah is in the last third of the night'],
            ['title' => 'Create weekly family learning circle', 'domain' => 'family', 'frequency' => 'weekly', 'alignment' => 'Raising righteous, knowledgeable children'],
            ['title' => 'Mentor a young person in the community', 'domain' => 'community', 'frequency' => 'weekly', 'alignment' => 'Being a source of light and guidance in the community'],
            ['title' => 'Document life lessons in journal', 'domain' => 'knowledge', 'frequency' => 'daily', 'alignment' => 'Building a repository of wisdom that outlives you'],
            ['title' => 'Volunteer at local education initiative', 'domain' => 'community', 'frequency' => 'weekly', 'alignment' => 'Using skills to create opportunities for others'],
            ['title' => 'Deep work on professional project', 'domain' => 'professional', 'frequency' => 'daily', 'alignment' => 'Excellence in work as a form of ibadah'],
        ];

        $actionIds = [];
        foreach ($actions as $i => $action) {
            $actionId = Str::uuid()->toString();
            $actionIds[] = $actionId;
            DB::table('actions')->insert(array_merge($action, [
                'id' => $actionId,
                'user_id' => $userId,
                'is_active' => true,
                'sort_order' => $i,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now,
            ]));
        }

        // 4. Create 7 days of check-ins
        $moods = ['grateful', 'peaceful', 'motivated', 'reflective', 'hopeful', 'content', 'energized'];
        $gratitudes = [
            'Grateful for the gift of another day to serve Allah and my family',
            'Alhamdulillah for the health and strength to pursue my vision',
            'Thankful for the knowledge I was able to share today',
            'Grateful for the patience Allah granted me during a difficult moment',
            'Thankful for my family\'s love and support in this journey',
            'Alhamdulillah for the community that surrounds and uplifts me',
            'Grateful for the clarity of purpose that guides my daily actions',
        ];
        $struggles = [
            'Struggled with consistency in my morning routine',
            'Found it hard to balance work and family time',
            'Felt overwhelmed by the gap between where I am and where I want to be',
            null,
            'Difficulty in staying patient with slow progress',
            null,
            'Felt distracted during prayer today',
        ];
        $duas = [
            'Ya Allah, grant me istiqamah on this path',
            'O Allah, make me a source of benefit wherever I go',
            'Ya Rabb, soften my heart and increase my knowledge',
            'Allah, guide my family and keep us united in goodness',
            null,
            'Ya Allah, make my legacy one that pleases You',
            'O Allah, grant me tawfiq in all that I do',
        ];

        for ($day = 6; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day)->toDateString();
            DB::table('check_ins')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'check_in_date' => $date,
                'gratitude' => $gratitudes[$day],
                'struggle' => $struggles[$day],
                'dua' => $duas[$day],
                'tawakkul_moment' => $day % 2 === 0 ? 'Trusted Allah\'s plan when things didn\'t go as expected' : null,
                'created_at' => $now->copy()->subDays($day),
                'updated_at' => $now->copy()->subDays($day),
            ]);
        }

        // 5. Create journal entries
        $journals = [
            ['prompt' => 'What does your ideal day look like when you are living your vision?', 'content' => 'My ideal day begins with Fajr prayer in congregation, followed by morning adhkar and reflection. I spend time reading and learning before starting meaningful work that serves the community. After Dhuhr, I take a walk and reflect on my blessings. The evening is reserved for family — dinner together, learning circle with my children, and heartfelt conversations. I end the day with tahajjud, making dua for my family and ummah. Every moment is intentional, every action aligned with my purpose.'],
            ['prompt' => 'Reflect on a moment this week where you saw your identity traits in action.', 'content' => 'This week, I noticed my trait of patience (sabr) manifest beautifully during a community disagreement. Instead of reacting, I listened deeply, held space for different perspectives, and helped the group find common ground. It reminded me that true leadership is not about having all the answers, but about creating the conditions for wisdom to emerge. Alhamdulillah for this growth.'],
            ['prompt' => 'What legacy are you building today through your smallest actions?', 'content' => 'Today I realized that legacy isn\'t built in grand gestures alone. When I patiently explained a concept to my child, when I smiled at a stranger, when I chose integrity over convenience — these are the bricks of my legacy. Each small action is a seed planted for a harvest I may never see, but trust will grow insha\'Allah.'],
            ['prompt' => 'How has your vision shaped a decision you made recently?', 'content' => 'I was offered a lucrative project that would have consumed my evenings and weekends for three months. My old self would have jumped at it. But my vision — to be present for my family and leave behind values, not just wealth — gave me clarity. I politely declined and instead invested that time in my family learning circle. The look of joy on my children\'s faces confirmed I made the right choice.'],
            ['prompt' => 'Write about a challenge you\'re facing and how your faith guides you through it.', 'content' => 'I\'m struggling with the pace of my community project. Progress is slower than I hoped, and sometimes I wonder if I\'m making any difference at all. But then I remember: "Indeed, with hardship comes ease" (94:6). My role is effort and intention; the results belong to Allah. I find peace in tawakkul — doing my absolute best while trusting His perfect plan. This challenge is not a obstacle, it\'s a teacher.'],
        ];

        foreach ($journals as $i => $journal) {
            DB::table('journal_entries')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'prompt' => $journal['prompt'],
                'content' => $journal['content'],
                'is_shared' => $i === 0,
                'created_at' => $now->copy()->subDays($i * 2),
                'updated_at' => $now->copy()->subDays($i * 2),
            ]);
        }

        // 6. Create action check-ins (10 across various days and actions)
        $actionCheckIns = [
            [0, 0, 'done'], [1, 0, 'done'], [2, 0, 'done'], // today: first 3 actions done
            [0, 1, 'done'], [1, 1, 'done'], [3, 1, 'done'], // yesterday
            [0, 2, 'done'], [4, 2, 'done'],                   // 2 days ago
            [0, 3, 'done'], [7, 3, 'done'],                   // 3 days ago
        ];

        foreach ($actionCheckIns as $aci) {
            [$actionIdx, $daysAgo, $status] = $aci;
            if (isset($actionIds[$actionIdx])) {
                DB::table('action_check_ins')->insert([
                    'id' => Str::uuid()->toString(),
                    'action_id' => $actionIds[$actionIdx],
                    'user_id' => $userId,
                    'check_in_date' => $now->copy()->subDays($daysAgo)->toDateString(),
                    'status' => $status,
                    'mood' => $moods[$daysAgo % count($moods)],
                    'energy_level' => rand(6, 9),
                    'created_at' => $now->copy()->subDays($daysAgo),
                ]);
            }
        }

        // 7. Assign traits
        $traitNames = ['Patient (Sabr)', 'Grateful (Shukr)', 'Wise (Hikma)', 'Generous (Karam)', 'Trustworthy (Amana)', 'Reflective (Tafakkur)'];
        $traitIds = DB::table('traits')->whereIn('name', $traitNames)->pluck('id')->toArray();
        // If exact names don't match, just take first 6
        if (count($traitIds) < 5) {
            $traitIds = DB::table('traits')->limit(6)->pluck('id')->toArray();
        }

        foreach ($traitIds as $i => $traitId) {
            DB::table('user_traits')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'trait_id' => $traitId,
                'sort_order' => $i,
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now,
            ]);
        }

        // 8. Create timeline events
        $timelineEvents = [
            ['event_type' => 'action_completed', 'category' => 'actions', 'title' => 'Morning Adhkar Completed', 'description' => 'Finished morning remembrance routine', 'hours_ago' => 0.5],
            ['event_type' => 'journal_created', 'category' => 'journal', 'title' => 'Gratitude Entry Written', 'description' => 'Reflected on 3 blessings from yesterday', 'hours_ago' => 1],
            ['event_type' => 'action_completed', 'category' => 'actions', 'title' => 'Read 20 Pages', 'description' => "Continued reading 'Revive Your Heart' by Nouman Ali Khan", 'hours_ago' => 3],
            ['event_type' => 'vision_updated', 'category' => 'vision', 'title' => 'Vision Statement Updated', 'description' => 'Refined 5-year community impact vision', 'hours_ago' => 5],
            ['event_type' => 'action_completed', 'category' => 'actions', 'title' => 'Charity Donation Made', 'description' => 'Contributed to local masjid renovation fund', 'hours_ago' => 26],
            ['event_type' => 'trait_added', 'category' => 'identity', 'title' => 'Trait Added: Patient Leader', 'description' => "Added 'Patient Leader' to identity board", 'hours_ago' => 28],
            ['event_type' => 'journal_created', 'category' => 'journal', 'title' => 'Weekly Reflection Written', 'description' => 'Reviewed progress on 12 completed actions this week', 'hours_ago' => 30],
            ['event_type' => 'vision_updated', 'category' => 'vision', 'title' => 'Akhirah Intention Reviewed', 'description' => 'Re-read and affirmed core intention', 'hours_ago' => 32],
            ['event_type' => 'action_completed', 'category' => 'actions', 'title' => 'Volunteered at Food Bank', 'description' => '3-hour shift packing meals for families', 'hours_ago' => 51],
            ['event_type' => 'trait_removed', 'category' => 'identity', 'title' => 'Trait Removed: Perfectionist', 'description' => "Replaced with 'Excellence-Driven' for healthier framing", 'hours_ago' => 73],
            ['event_type' => 'journal_created', 'category' => 'journal', 'title' => 'Dream Journal Entry', 'description' => 'Recorded a vivid dream about teaching overseas', 'hours_ago' => 79],
            ['event_type' => 'action_created', 'category' => 'actions', 'title' => 'New Action Created', 'description' => "Added 'Memorise Surah Al-Mulk' to weekly plan", 'hours_ago' => 98],
            ['event_type' => 'vision_created', 'category' => 'vision', 'title' => 'Future World Vision Created', 'description' => 'Drafted initial community education vision', 'hours_ago' => 101],
            ['event_type' => 'trait_added', 'category' => 'identity', 'title' => 'Identity Board Created', 'description' => 'Set up initial 5 identity traits', 'hours_ago' => 147],
            ['event_type' => 'action_completed', 'category' => 'actions', 'title' => 'Completed Fajr Challenge', 'description' => '7-day streak of praying Fajr on time', 'hours_ago' => 169],
            ['event_type' => 'journal_created', 'category' => 'journal', 'title' => 'Forgiveness Letter Written', 'description' => 'Wrote and released a forgiveness letter', 'hours_ago' => 196],
            ['event_type' => 'vision_created', 'category' => 'vision', 'title' => 'Legacy Statement Drafted', 'description' => 'Wrote first draft of personal legacy statement', 'hours_ago' => 218],
        ];

        foreach ($timelineEvents as $event) {
            DB::table('timeline_events')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'event_type' => $event['event_type'],
                'category' => $event['category'],
                'title' => $event['title'],
                'description' => $event['description'],
                'created_at' => $now->copy()->subHours($event['hours_ago']),
            ]);
        }

        // 9. Create review
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        DB::table('reviews')->insert([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'review_type' => 'monthly',
            'period_start' => $now->copy()->subMonth()->startOfMonth()->toDateString(),
            'period_end' => $now->copy()->subMonth()->endOfMonth()->toDateString(),
            'vision_reflection' => 'My vision has become clearer this month. I can see the direct connection between my daily actions and the legacy I want to leave.',
            'identity_reflection' => 'I have grown in patience and gratitude. These traits are becoming more natural, less forced.',
            'action_reflection' => 'Consistency improved significantly. Morning dhikr is now automatic. Family learning circle is the highlight of our week.',
            'overall_reflection' => 'Alhamdulillah, this has been a month of steady growth. The SEE-BE-DO framework is becoming second nature.',
            'domain_ratings' => json_encode(['spiritual' => 8, 'knowledge' => 7, 'family' => 9, 'health' => 6, 'professional' => 7, 'community' => 8]),
            'created_at' => $now->copy()->subMonth()->endOfMonth(),
        ]);

        $this->command->info("✅ Test user created: test@visionaries.pro / password");
        $this->command->info("   User ID: {$userId}");
        $this->command->info("   Vision, actions, check-ins, journal, traits, timeline, and reviews seeded.");
    }
}
