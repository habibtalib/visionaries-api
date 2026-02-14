<?php
namespace Database\Seeders;

use App\Models\Trait_;
use Illuminate\Database\Seeder;

class TraitSeeder extends Seeder
{
    public function run(): void
    {
        $traits = [
            [
                'name' => 'Patient (Sabr)', 
                'name_ms' => 'Sabar',
                'category' => 'Spiritual', 
                'description' => 'Enduring hardship with grace and trust in Allah\'s plan', 
                'description_ms' => 'Menghadapi kesusahan dengan tenang dan percaya kepada rancangan Allah',
                'why_template' => 'Patience is the foundation of all virtue in Islam', 
                'why_template_ms' => 'Sabar adalah asas semua kebaikan dalam Islam',
                'daily_template' => 'Pause before reacting. Take 3 breaths. Respond with wisdom.', 
                'daily_template_ms' => 'Berhenti sejenak sebelum bertindak balas. Ambil 3 nafas. Balas dengan hikmah.',
                'opposite_template' => 'Impulsiveness, irritability, giving up quickly',
                'opposite_template_ms' => 'Tergesa-gesa, mudah marah, cepat berputus asa'
            ],
            [
                'name' => 'Grateful (Shukr)', 
                'name_ms' => 'Bersyukur',
                'category' => 'Spiritual', 
                'description' => 'Recognizing and appreciating Allah\'s blessings', 
                'description_ms' => 'Mengenali dan menghargai nikmat Allah',
                'why_template' => 'Gratitude increases blessings and contentment', 
                'why_template_ms' => 'Syukur menambah nikmat dan kepuasan hati',
                'daily_template' => 'List 3 blessings each morning. Say Alhamdulillah consciously.', 
                'daily_template_ms' => 'Senaraikan 3 nikmat setiap pagi. Ucap Alhamdulillah dengan sedar.',
                'opposite_template' => 'Complaining, entitlement, taking things for granted',
                'opposite_template_ms' => 'Mengeluh, rasa berhak, mengambil mudah nikmat'
            ],
            [
                'name' => 'Humble (Tawadhu)', 
                'name_ms' => 'Rendah Diri',
                'category' => 'Character', 
                'description' => 'Lowering oneself before Allah and treating others with respect', 
                'description_ms' => 'Merendah diri di hadapan Allah dan melayan orang lain dengan hormat',
                'why_template' => 'Humility opens the heart to learning and connection', 
                'why_template_ms' => 'Kerendahan hati membuka hati untuk belajar dan berhubung',
                'daily_template' => 'Listen more than you speak. Acknowledge others\' contributions.', 
                'daily_template_ms' => 'Dengar lebih daripada bercakap. Hargai sumbangan orang lain.',
                'opposite_template' => 'Arrogance, self-importance, dismissing others',
                'opposite_template_ms' => 'Sombong, rasa diri penting, meremehkan orang lain'
            ],
            [
                'name' => 'Generous (Karam)', 
                'name_ms' => 'Murah Hati',
                'category' => 'Social', 
                'description' => 'Giving freely of time, wealth, and kindness', 
                'description_ms' => 'Memberi dengan rela masa, harta, dan kebaikan',
                'why_template' => 'Generosity purifies the soul and strengthens community', 
                'why_template_ms' => 'Kemurahan hati menyucikan jiwa dan mengukuhkan komuniti',
                'daily_template' => 'Give something to someone daily — a smile, time, or charity.', 
                'daily_template_ms' => 'Beri sesuatu kepada seseorang setiap hari — senyuman, masa, atau sedekah.',
                'opposite_template' => 'Stinginess, hoarding, withholding help',
                'opposite_template_ms' => 'Kedekut, menimbun, enggan membantu'
            ],
            [
                'name' => 'Truthful (Sidq)', 
                'name_ms' => 'Jujur',
                'category' => 'Character', 
                'description' => 'Speaking and living in alignment with truth', 
                'description_ms' => 'Berkata dan hidup selaras dengan kebenaran',
                'why_template' => 'Truth is the path of the prophets', 
                'why_template_ms' => 'Kebenaran adalah jalan para nabi',
                'daily_template' => 'Speak honestly even when difficult. Keep promises.', 
                'daily_template_ms' => 'Berkata jujur walaupun sukar. Tunaikan janji.',
                'opposite_template' => 'Lying, exaggeration, breaking promises',
                'opposite_template_ms' => 'Berbohong, membesar-besarkan, mengingkari janji'
            ],
            [
                'name' => 'Just (Adl)', 
                'name_ms' => 'Adil',
                'category' => 'Character', 
                'description' => 'Treating all people fairly regardless of relationship', 
                'description_ms' => 'Melayan semua orang dengan adil tanpa mengira hubungan',
                'why_template' => 'Justice is commanded by Allah in all affairs', 
                'why_template_ms' => 'Keadilan diperintah oleh Allah dalam semua urusan',
                'daily_template' => 'Consider all sides before judging. Stand for what\'s right.', 
                'daily_template_ms' => 'Pertimbang semua pihak sebelum menghakimi. Berdiri untuk kebenaran.',
                'opposite_template' => 'Favoritism, bias, ignoring injustice',
                'opposite_template_ms' => 'Pilih kasih, berat sebelah, mengabaikan ketidakadilan'
            ],
            [
                'name' => 'Compassionate (Rahma)', 
                'name_ms' => 'Penyayang',
                'category' => 'Social', 
                'description' => 'Showing mercy and empathy to all creation', 
                'description_ms' => 'Menunjukkan belas kasihan dan empati kepada semua makhluk',
                'why_template' => 'The Prophet (SAW) was sent as a mercy to all worlds', 
                'why_template_ms' => 'Nabi (SAW) diutus sebagai rahmat untuk semua alam',
                'daily_template' => 'Check on someone who might be struggling. Be gentle in speech.', 
                'daily_template_ms' => 'Tanya khabar seseorang yang mungkin bergelut. Lemah lembut dalam berkata.',
                'opposite_template' => 'Harshness, indifference to suffering, cruelty',
                'opposite_template_ms' => 'Kasar, tidak peduli penderitaan, kejam'
            ],
            [
                'name' => 'Forgiving (Afw)', 
                'name_ms' => 'Pemaaf',
                'category' => 'Spiritual', 
                'description' => 'Letting go of grudges and pardoning others', 
                'description_ms' => 'Melepaskan dendam dan memaafkan orang lain',
                'why_template' => 'Forgiveness frees the heart and earns Allah\'s forgiveness', 
                'why_template_ms' => 'Keampunan membebaskan hati dan meraih ampunan Allah',
                'daily_template' => 'Release one resentment. Make dua for someone who wronged you.', 
                'daily_template_ms' => 'Lepaskan satu dendam. Berdoa untuk orang yang telah menyakiti anda.',
                'opposite_template' => 'Holding grudges, seeking revenge, bitterness',
                'opposite_template_ms' => 'Memendam dendam, mencari balas dendam, kepahitan'
            ],
            [
                'name' => 'Wise (Hikma)', 
                'name_ms' => 'Bijaksana',
                'category' => 'Character', 
                'description' => 'Acting with discernment and deep understanding', 
                'description_ms' => 'Bertindak dengan pertimbangan dan pemahaman mendalam',
                'why_template' => 'Wisdom is a gift from Allah that guides right action', 
                'why_template_ms' => 'Hikmah adalah kurnia Allah yang membimbing tindakan benar',
                'daily_template' => 'Think before speaking. Seek knowledge before acting.', 
                'daily_template_ms' => 'Berfikir sebelum berkata. Cari ilmu sebelum bertindak.',
                'opposite_template' => 'Recklessness, ignorance, hasty decisions',
                'opposite_template_ms' => 'Melulu, jahil, keputusan tergesa-gesa'
            ],
            [
                'name' => 'Courageous (Shuja\'a)', 
                'name_ms' => 'Berani',
                'category' => 'Character', 
                'description' => 'Standing firm in conviction despite fear', 
                'description_ms' => 'Berdiri teguh dengan pendirian walaupun takut',
                'why_template' => 'Courage is needed to live by principle in a challenging world', 
                'why_template_ms' => 'Keberanian diperlukan untuk hidup berprinsip di dunia yang mencabar',
                'daily_template' => 'Do one thing that scares you. Speak truth when it\'s hard.', 
                'daily_template_ms' => 'Lakukan satu perkara yang menakutkan anda. Cakap benar walaupun sukar.',
                'opposite_template' => 'Cowardice, people-pleasing, avoiding difficulty',
                'opposite_template_ms' => 'Penakut, mengambil hati orang, mengelak kesukaran'
            ],
            [
                'name' => 'Disciplined (Istiqama)', 
                'name_ms' => 'Istiqamah',
                'category' => 'Character', 
                'description' => 'Maintaining consistency in worship and good habits', 
                'description_ms' => 'Mengekalkan ketekalan dalam ibadah dan kebiasaan baik',
                'why_template' => 'Steadfastness is more beloved to Allah than occasional intensity', 
                'why_template_ms' => 'Ketekalan lebih disukai Allah daripada intensiti sekali-sekala',
                'daily_template' => 'Keep your daily routines. Show up even when motivation is low.', 
                'daily_template_ms' => 'Kekalkan rutin harian. Hadir walaupun motivasi rendah.',
                'opposite_template' => 'Inconsistency, laziness, giving in to whims',
                'opposite_template_ms' => 'Tidak konsisten, malas, mengikut kehendak nafsu'
            ],
            [
                'name' => 'Reflective (Tafakkur)', 
                'name_ms' => 'Berfikir',
                'category' => 'Spiritual', 
                'description' => 'Contemplating deeply on life, creation, and purpose', 
                'description_ms' => 'Berfikir mendalam tentang kehidupan, ciptaan, dan tujuan',
                'why_template' => 'Reflection is worship — thinking deeply about Allah\'s signs', 
                'why_template_ms' => 'Berfikir adalah ibadah — merenungkan tanda-tanda Allah',
                'daily_template' => 'Spend 10 minutes in quiet contemplation. Journal your thoughts.', 
                'daily_template_ms' => 'Luangkan 10 minit untuk renungan sunyi. Tulis fikiran anda.',
                'opposite_template' => 'Mindlessness, distraction, superficiality',
                'opposite_template_ms' => 'Lalai, terganggu, cetek'
            ],
            [
                'name' => 'Hopeful (Raja)', 
                'name_ms' => 'Penuh Harapan',
                'category' => 'Spiritual', 
                'description' => 'Maintaining optimism and hope in Allah\'s mercy', 
                'description_ms' => 'Mengekalkan optimisme dan harapan pada rahmat Allah',
                'why_template' => 'Hope in Allah prevents despair and motivates good action', 
                'why_template_ms' => 'Harapan kepada Allah mencegah putus asa dan mendorong amal soleh',
                'daily_template' => 'Remember Allah\'s promises. Focus on possibilities, not limitations.', 
                'daily_template_ms' => 'Ingat janji-janji Allah. Fokus pada kemungkinan, bukan batasan.',
                'opposite_template' => 'Despair, pessimism, losing faith',
                'opposite_template_ms' => 'Berputus asa, pesimis, hilang iman'
            ],
            [
                'name' => 'Trustworthy (Amana)', 
                'name_ms' => 'Amanah',
                'category' => 'Social', 
                'description' => 'Being reliable and honoring trusts placed in you', 
                'description_ms' => 'Boleh dipercayai dan menghormati amanah yang diberikan',
                'why_template' => 'Trust is the foundation of all relationships and community', 
                'why_template_ms' => 'Amanah adalah asas semua hubungan dan komuniti',
                'daily_template' => 'Follow through on commitments. Guard what\'s entrusted to you.', 
                'daily_template_ms' => 'Tunaikan komitmen. Jaga apa yang diamanahkan kepada anda.',
                'opposite_template' => 'Betrayal, unreliability, breaking confidence',
                'opposite_template_ms' => 'Khianat, tidak boleh dipercayai, mencabuli kepercayaan'
            ],
        ];

        foreach ($traits as $trait) {
            Trait_::updateOrCreate(['name' => $trait['name']], $trait);
        }
    }
}