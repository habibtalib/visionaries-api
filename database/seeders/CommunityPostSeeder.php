<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityPostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // Use first user for all posts, or create multiple users
        if (!$user) {
            return; // No users yet
        }

        $posts = [
            [
                'author' => 'Amira S.',
                'content' => "Today I'm grateful for the quiet morning hours before Fajr. The stillness allows me to reconnect with my intentions and remember why I started this journey of self-improvement.",
                'prompt_badge' => 'Gratitude',
                'likes_count' => 42,
                'comments_count' => 7,
                'shares_count' => 3,
            ],
            [
                'author' => 'Khalid M.',
                'content' => "I revisited my 5-year vision today and realized how much has already shifted. The core values remain — service, knowledge, family — but the path looks different. And that's okay. The destination is clear even if the route changes.",
                'prompt_badge' => 'Vision',
                'likes_count' => 58,
                'comments_count' => 12,
                'shares_count' => 8,
            ],
            [
                'author' => 'Fatimah R.',
                'content' => "Am I becoming who I said I want to be? A challenging situation at work tested my patience today. I remembered my vision of being someone who responds with wisdom, not reaction. Small progress, but progress nonetheless.",
                'prompt_badge' => 'Identity',
                'likes_count' => 91,
                'comments_count' => 15,
                'shares_count' => 11,
            ],
            [
                'author' => 'Yusuf A.',
                'content' => "I learned that consistency matters more than intensity. My daily 10-minute reflection practice has taught me more about myself than occasional hour-long sessions ever did. The compound effect is real.",
                'prompt_badge' => 'Reflection',
                'likes_count' => 67,
                'comments_count' => 9,
                'shares_count' => 5,
            ],
            [
                'author' => 'Nadia K.',
                'content' => "Growth isn't linear. Some days feel like setbacks, but looking at my journal entries from a month ago, I can see how far I've come in embodying patience and gratitude. Trust the process.",
                'prompt_badge' => 'Growth',
                'likes_count' => 103,
                'comments_count' => 18,
                'shares_count' => 14,
            ],
            [
                'author' => 'Ibrahim H.',
                'content' => "Sabr isn't passive waiting — it's active perseverance while trusting Allah's timing. Today I practiced patience not by doing nothing, but by doing the right thing even when results weren't immediate.",
                'prompt_badge' => 'Patience',
                'likes_count' => 124,
                'comments_count' => 22,
                'shares_count' => 19,
            ],
            [
                'author' => 'Layla T.',
                'content' => "Asked myself today: 'If money and status didn't matter, what would I spend my life doing?' The answer surprised me — teaching. Not in a classroom, but mentoring young Muslims to find their own vision.",
                'prompt_badge' => 'Purpose',
                'likes_count' => 88,
                'comments_count' => 14,
                'shares_count' => 7,
            ],
            [
                'author' => 'Omar Z.',
                'content' => "Had a difficult conversation I'd been avoiding for weeks. It wasn't easy, but it was necessary. Courage isn't the absence of fear — it's choosing growth over comfort. Alhamdulillah for the strength.",
                'prompt_badge' => 'Courage',
                'likes_count' => 76,
                'comments_count' => 11,
                'shares_count' => 6,
            ],
            [
                'author' => 'Hana B.',
                'content' => "Grateful for the people Allah placed in my life who remind me of my worth when I forget. A single kind word from a friend today shifted my entire perspective. Never underestimate the power of encouragement.",
                'prompt_badge' => 'Gratitude',
                'likes_count' => 115,
                'comments_count' => 20,
                'shares_count' => 16,
            ],
            [
                'author' => 'Tariq N.',
                'content' => "My vision board isn't about material goals — it's about the character I want to embody. Generous. Patient. Present. Every action I take today is a vote for the person I'm becoming.",
                'prompt_badge' => 'Vision',
                'likes_count' => 97,
                'comments_count' => 13,
                'shares_count' => 10,
            ],
        ];

        foreach ($posts as $index => $postData) {
            CommunityPost::create([
                'user_id' => $user->id,
                'content' => $postData['content'],
                'prompt_badge' => $postData['prompt_badge'],
                'likes_count' => $postData['likes_count'],
                'comments_count' => $postData['comments_count'],
                'shares_count' => $postData['shares_count'],
                'type' => 'post',
                'created_at' => now()->subHours($index * 2), // Space posts 2 hours apart
            ]);
        }
    }
}