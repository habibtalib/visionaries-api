<?php
namespace Database\Seeders;

use App\Models\IslamicEvent;
use Illuminate\Database\Seeder;

class IslamicEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            ['name_en' => 'Ramadan Begins', 'name_ar' => 'بداية رمضان', 'name_ms' => 'Permulaan Ramadan', 'type' => 'fasting', 'description_en' => 'The beginning of the holy month of fasting', 'description_ms' => 'Permulaan bulan suci puasa', 'hijri_month' => 9, 'hijri_day' => 1, 'gregorian_date_2026' => '2026-02-18', 'color' => '#10B981'],
            ['name_en' => 'Laylat al-Qadr', 'name_ar' => 'ليلة القدر', 'name_ms' => 'Malam Lailatulqadar', 'type' => 'holy_night', 'description_en' => 'The Night of Power, better than a thousand months', 'description_ms' => 'Malam yang lebih baik daripada seribu bulan', 'hijri_month' => 9, 'hijri_day' => 27, 'gregorian_date_2026' => '2026-03-16', 'color' => '#8B5CF6'],
            ['name_en' => 'Eid al-Fitr', 'name_ar' => 'عيد الفطر', 'name_ms' => 'Hari Raya Aidilfitri', 'type' => 'eid', 'description_en' => 'Festival of Breaking the Fast', 'description_ms' => 'Hari Raya Aidilfitri', 'hijri_month' => 10, 'hijri_day' => 1, 'gregorian_date_2026' => '2026-03-20', 'color' => '#F59E0B'],
            ['name_en' => 'Day of Arafah', 'name_ar' => 'يوم عرفة', 'name_ms' => 'Hari Arafah', 'type' => 'remembrance', 'description_en' => 'The most important day of Hajj', 'description_ms' => 'Hari paling penting dalam ibadah Haji', 'hijri_month' => 12, 'hijri_day' => 9, 'gregorian_date_2026' => '2026-05-26', 'color' => '#3B82F6'],
            ['name_en' => 'Eid al-Adha', 'name_ar' => 'عيد الأضحى', 'name_ms' => 'Hari Raya Aidiladha', 'type' => 'eid', 'description_en' => 'Festival of Sacrifice', 'description_ms' => 'Hari Raya Korban', 'hijri_month' => 12, 'hijri_day' => 10, 'gregorian_date_2026' => '2026-05-27', 'color' => '#EF4444'],
            ['name_en' => 'Islamic New Year', 'name_ar' => 'رأس السنة الهجرية', 'name_ms' => 'Awal Muharram', 'type' => 'remembrance', 'description_en' => 'Beginning of the new Hijri year', 'description_ms' => 'Permulaan tahun baru Hijrah', 'hijri_month' => 1, 'hijri_day' => 1, 'gregorian_date_2026' => '2026-06-17', 'color' => '#6366F1'],
            ['name_en' => 'Ashura', 'name_ar' => 'عاشوراء', 'name_ms' => 'Hari Asyura', 'type' => 'fasting', 'description_en' => 'Day of fasting on 10th Muharram', 'description_ms' => 'Hari puasa sunat 10 Muharram', 'hijri_month' => 1, 'hijri_day' => 10, 'gregorian_date_2026' => '2026-06-26', 'color' => '#14B8A6'],
            ['name_en' => 'Mawlid al-Nabi', 'name_ar' => 'المولد النبوي', 'name_ms' => 'Maulidur Rasul', 'type' => 'remembrance', 'description_en' => 'Birthday of Prophet Muhammad ﷺ', 'description_ms' => 'Hari kelahiran Nabi Muhammad ﷺ', 'hijri_month' => 3, 'hijri_day' => 12, 'gregorian_date_2026' => '2026-08-27', 'color' => '#EC4899'],
        ];

        foreach ($events as $event) {
            IslamicEvent::create($event);
        }
    }
}
