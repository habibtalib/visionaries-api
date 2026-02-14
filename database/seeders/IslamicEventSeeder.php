<?php
namespace Database\Seeders;

use App\Models\IslamicEvent;
use Illuminate\Database\Seeder;

class IslamicEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Ramadan Begins',
                'title_ms' => 'Permulaan Ramadan', 
                'description' => 'The blessed month of fasting begins. A time for spiritual reflection, increased devotion, and self-discipline through abstaining from food and drink from dawn to sunset.',
                'event_date' => '2026-02-18',
                'hijri_date' => '1 Ramadan 1448',
                'type' => 'fasting',
                'is_recurring' => true
            ],
            [
                'title' => 'Laylat al-Qadr',
                'title_ms' => 'Malam Lailatulqadar', 
                'description' => 'The Night of Power — better than a thousand months. Seek it in the odd nights of the last ten days of Ramadan. Angels descend and peace prevails until dawn.',
                'event_date' => '2026-03-15',
                'hijri_date' => '27 Ramadan 1448',
                'type' => 'holy',
                'is_recurring' => true
            ],
            [
                'title' => 'Eid al-Fitr',
                'title_ms' => 'Hari Raya Aidilfitri', 
                'description' => 'Festival of Breaking the Fast. A day of joy, gratitude, and celebration after completing Ramadan. Muslims gather for prayer, give charity, and feast together.',
                'event_date' => '2026-03-20',
                'hijri_date' => '1 Shawwal 1448',
                'type' => 'eid',
                'is_recurring' => true
            ],
            [
                'title' => 'Isra & Mi\'raj',
                'title_ms' => 'Israk Mikraj', 
                'description' => 'The Night Journey and Ascension. Prophet Muhammad ﷺ traveled from Mecca to Jerusalem and ascended through the heavens, where the five daily prayers were prescribed.',
                'event_date' => '2026-01-16',
                'hijri_date' => '27 Rajab 1447',
                'type' => 'holy',
                'is_recurring' => true
            ],
            [
                'title' => 'Day of Arafah',
                'title_ms' => 'Hari Arafah', 
                'description' => 'The most blessed day of the year. Standing on Arafah is the pillar of Hajj. Fasting on this day expiates sins of the previous and coming year.',
                'event_date' => '2026-05-26',
                'hijri_date' => '9 Dhul Hijjah 1447',
                'type' => 'fasting',
                'is_recurring' => true
            ],
            [
                'title' => 'Eid al-Adha',
                'title_ms' => 'Hari Raya Aidiladha', 
                'description' => 'Festival of Sacrifice. Commemorates Prophet Ibrahim\'s willingness to sacrifice his son in obedience to Allah. Muslims perform the Qurbani and share meat with those in need.',
                'event_date' => '2026-05-27',
                'hijri_date' => '10 Dhul Hijjah 1447',
                'type' => 'eid',
                'is_recurring' => true
            ],
            [
                'title' => 'Ashura',
                'title_ms' => 'Hari Asyura', 
                'description' => 'The 10th of Muharram. A day of great historical significance. Fasting on this day, along with the 9th or 11th, expiates sins of the previous year.',
                'event_date' => '2026-07-25',
                'hijri_date' => '10 Muharram 1448',
                'type' => 'remembrance',
                'is_recurring' => true
            ],
            [
                'title' => 'Mawlid al-Nabi',
                'title_ms' => 'Maulidur Rasul', 
                'description' => 'Birth of Prophet Muhammad ﷺ. A day to remember his life, teachings, and noble character — the mercy sent to all of mankind.',
                'event_date' => '2026-09-24',
                'hijri_date' => '12 Rabi al-Awwal 1448',
                'type' => 'remembrance',
                'is_recurring' => true
            ],
        ];

        foreach ($events as $event) {
            IslamicEvent::firstOrCreate(
                ['title' => $event['title']],
                $event
            );
        }
    }
}