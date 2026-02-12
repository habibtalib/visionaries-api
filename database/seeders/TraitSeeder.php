<?php
namespace Database\Seeders;

use App\Models\Trait_;
use Illuminate\Database\Seeder;

class TraitSeeder extends Seeder
{
    public function run(): void
    {
        $traits = [
            ['name' => 'Patient (Sabr)', 'category' => 'Spiritual', 'description' => 'Enduring hardship with grace and trust in Allah\'s plan', 'why_template' => 'Patience is the foundation of all virtue in Islam', 'daily_template' => 'Pause before reacting. Take 3 breaths. Respond with wisdom.', 'opposite_template' => 'Impulsiveness, irritability, giving up quickly'],
            ['name' => 'Grateful (Shukr)', 'category' => 'Spiritual', 'description' => 'Recognizing and appreciating Allah\'s blessings', 'why_template' => 'Gratitude increases blessings and contentment', 'daily_template' => 'List 3 blessings each morning. Say Alhamdulillah consciously.', 'opposite_template' => 'Complaining, entitlement, taking things for granted'],
            ['name' => 'Humble (Tawadhu)', 'category' => 'Character', 'description' => 'Lowering oneself before Allah and treating others with respect', 'why_template' => 'Humility opens the heart to learning and connection', 'daily_template' => 'Listen more than you speak. Acknowledge others\' contributions.', 'opposite_template' => 'Arrogance, self-importance, dismissing others'],
            ['name' => 'Generous (Karam)', 'category' => 'Social', 'description' => 'Giving freely of time, wealth, and kindness', 'why_template' => 'Generosity purifies the soul and strengthens community', 'daily_template' => 'Give something to someone daily — a smile, time, or charity.', 'opposite_template' => 'Stinginess, hoarding, withholding help'],
            ['name' => 'Truthful (Sidq)', 'category' => 'Character', 'description' => 'Speaking and living in alignment with truth', 'why_template' => 'Truth is the path of the prophets', 'daily_template' => 'Speak honestly even when difficult. Keep promises.', 'opposite_template' => 'Lying, exaggeration, breaking promises'],
            ['name' => 'Just (Adl)', 'category' => 'Character', 'description' => 'Treating all people fairly regardless of relationship', 'why_template' => 'Justice is commanded by Allah in all affairs', 'daily_template' => 'Consider all sides before judging. Stand for what\'s right.', 'opposite_template' => 'Favoritism, bias, ignoring injustice'],
            ['name' => 'Compassionate (Rahma)', 'category' => 'Social', 'description' => 'Showing mercy and empathy to all creation', 'why_template' => 'The Prophet (SAW) was sent as a mercy to all worlds', 'daily_template' => 'Check on someone who might be struggling. Be gentle in speech.', 'opposite_template' => 'Harshness, indifference to suffering, cruelty'],
            ['name' => 'Forgiving (Afw)', 'category' => 'Spiritual', 'description' => 'Letting go of grudges and pardoning others', 'why_template' => 'Forgiveness frees the heart and earns Allah\'s forgiveness', 'daily_template' => 'Release one resentment. Make dua for someone who wronged you.', 'opposite_template' => 'Holding grudges, seeking revenge, bitterness'],
            ['name' => 'Wise (Hikma)', 'category' => 'Character', 'description' => 'Acting with discernment and deep understanding', 'why_template' => 'Wisdom is a gift from Allah that guides right action', 'daily_template' => 'Think before speaking. Seek knowledge before acting.', 'opposite_template' => 'Recklessness, ignorance, hasty decisions'],
            ['name' => 'Courageous (Shuja\'a)', 'category' => 'Character', 'description' => 'Standing firm in conviction despite fear', 'why_template' => 'Courage is needed to live by principle in a challenging world', 'daily_template' => 'Do one thing that scares you. Speak truth when it\'s hard.', 'opposite_template' => 'Cowardice, people-pleasing, avoiding difficulty'],
            ['name' => 'Disciplined (Istiqama)', 'category' => 'Character', 'description' => 'Maintaining consistency in worship and good habits', 'why_template' => 'Steadfastness is more beloved to Allah than occasional intensity', 'daily_template' => 'Keep your daily routines. Show up even when motivation is low.', 'opposite_template' => 'Inconsistency, laziness, giving in to whims'],
            ['name' => 'Reflective (Tafakkur)', 'category' => 'Spiritual', 'description' => 'Contemplating deeply on life, creation, and purpose', 'why_template' => 'Reflection is worship — thinking deeply about Allah\'s signs', 'daily_template' => 'Spend 10 minutes in quiet contemplation. Journal your thoughts.', 'opposite_template' => 'Mindlessness, distraction, superficiality'],
            ['name' => 'Hopeful (Raja)', 'category' => 'Spiritual', 'description' => 'Maintaining optimism and hope in Allah\'s mercy', 'why_template' => 'Hope in Allah prevents despair and motivates good action', 'daily_template' => 'Remember Allah\'s promises. Focus on possibilities, not limitations.', 'opposite_template' => 'Despair, pessimism, losing faith'],
            ['name' => 'Trustworthy (Amana)', 'category' => 'Social', 'description' => 'Being reliable and honoring trusts placed in you', 'why_template' => 'Trust is the foundation of all relationships and community', 'daily_template' => 'Follow through on commitments. Guard what\'s entrusted to you.', 'opposite_template' => 'Betrayal, unreliability, breaking confidence'],
        ];

        foreach ($traits as $trait) {
            Trait_::firstOrCreate(['name' => $trait['name']], $trait);
        }
    }
}
