<?php
namespace Database\Seeders;

use App\Models\Reel;
use Illuminate\Database\Seeder;

class ReelSeeder extends Seeder
{
    public function run(): void
    {
        $reels = [
            ['content' => '"The best of people are those who are most beneficial to others."', 'content_ms' => '"Sebaik-baik manusia ialah yang paling bermanfaat kepada manusia lain."', 'author' => 'Prophet Muhammad ﷺ', 'category' => 'hadith', 'gradient_from' => '#667eea', 'gradient_to' => '#764ba2'],
            ['content' => '"Verily, with hardship comes ease."', 'content_ms' => '"Sesungguhnya bersama kesulitan ada kemudahan."', 'author' => 'Quran 94:6', 'category' => 'quote', 'gradient_from' => '#f093fb', 'gradient_to' => '#f5576c'],
            ['content' => '"Be in this world as if you were a stranger or a traveler."', 'content_ms' => '"Jadilah di dunia ini seolah-olah kamu orang asing atau pengembara."', 'author' => 'Prophet Muhammad ﷺ', 'category' => 'hadith', 'gradient_from' => '#4facfe', 'gradient_to' => '#00f2fe'],
            ['content' => '"Your vision will become clear only when you can look into your own heart."', 'content_ms' => '"Visi anda hanya akan menjadi jelas apabila anda melihat ke dalam hati sendiri."', 'author' => 'Carl Jung', 'category' => 'reflection', 'gradient_from' => '#43e97b', 'gradient_to' => '#38f9d7'],
            ['content' => '"The soul that has no fixed purpose in life is lost."', 'content_ms' => '"Jiwa yang tiada tujuan tetap dalam hidup akan sesat."', 'author' => 'Imam al-Ghazali', 'category' => 'reflection', 'gradient_from' => '#fa709a', 'gradient_to' => '#fee140'],
            ['content' => '"Take account of yourselves before you are taken to account."', 'content_ms' => '"Hisablah diri kamu sebelum kamu dihisab."', 'author' => 'Umar ibn al-Khattab', 'category' => 'quote', 'gradient_from' => '#a18cd1', 'gradient_to' => '#fbc2eb'],
            ['content' => '"Knowledge without action is vanity, and action without knowledge is insanity."', 'content_ms' => '"Ilmu tanpa amalan adalah sia-sia, dan amalan tanpa ilmu adalah gila."', 'author' => 'Imam al-Ghazali', 'category' => 'reflection', 'gradient_from' => '#ffecd2', 'gradient_to' => '#fcb69f'],
            ['content' => '"Whoever treads a path seeking knowledge, Allah will ease for him a path to Paradise."', 'content_ms' => '"Sesiapa yang menempuh jalan mencari ilmu, Allah akan memudahkan baginya jalan ke syurga."', 'author' => 'Prophet Muhammad ﷺ', 'category' => 'hadith', 'gradient_from' => '#a1c4fd', 'gradient_to' => '#c2e9fb'],
            ['content' => '"Begin with the end in mind — but let the end be the akhirah."', 'content_ms' => '"Mulakan dengan matlamat akhir — tetapi biarkan matlamat akhir itu adalah akhirat."', 'author' => 'Visionaries Pro', 'category' => 'vision', 'gradient_from' => '#d4fc79', 'gradient_to' => '#96e6a1'],
            ['content' => '"Your legacy is not what you leave for people. It is what you leave in people."', 'content_ms' => '"Legasi anda bukan apa yang anda tinggalkan untuk orang. Ia adalah apa yang anda tinggalkan dalam diri orang."', 'author' => 'Visionaries Pro', 'category' => 'vision', 'gradient_from' => '#84fab0', 'gradient_to' => '#8fd3f4'],
        ];

        foreach ($reels as $reel) {
            Reel::create($reel);
        }
    }
}
