<?php
namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quizzes = [
            ['pillar' => 'see', 'question' => 'What is the first step in the SEE → BE → DO framework?', 'question_ms' => 'Apakah langkah pertama dalam rangka kerja SEE → BE → DO?', 'options' => [['text' => 'Define your actions', 'text_ms' => 'Tentukan tindakan anda'], ['text' => 'Clarify your vision', 'text_ms' => 'Jelaskan visi anda'], ['text' => 'Build habits', 'text_ms' => 'Bina tabiat'], ['text' => 'Find friends', 'text_ms' => 'Cari rakan']], 'correct_index' => 1, 'explanation' => 'SEE is about clarifying your vision — seeing where you want to go before taking action.', 'explanation_ms' => 'SEE adalah tentang menjelaskan visi anda — melihat ke mana anda ingin pergi sebelum bertindak.'],
            ['pillar' => 'see', 'question' => 'Which element is NOT part of vision crafting?', 'question_ms' => 'Elemen manakah yang BUKAN sebahagian daripada pembentukan visi?', 'options' => [['text' => 'Akhirah intention', 'text_ms' => 'Niat akhirat'], ['text' => 'Future world', 'text_ms' => 'Dunia masa depan'], ['text' => 'Daily schedule', 'text_ms' => 'Jadual harian'], ['text' => 'Legacy impact', 'text_ms' => 'Impak legasi']], 'correct_index' => 2, 'explanation' => 'Vision crafting focuses on akhirah intention, future world, and legacy impact.', 'explanation_ms' => 'Pembentukan visi tertumpu pada niat akhirat, dunia masa depan, dan impak legasi.'],
            ['pillar' => 'be', 'question' => 'What does the BE pillar focus on?', 'question_ms' => 'Apakah fokus tiang BE?', 'options' => [['text' => 'Setting goals', 'text_ms' => 'Menetapkan matlamat'], ['text' => 'Identity and character traits', 'text_ms' => 'Identiti dan sifat peribadi'], ['text' => 'Tracking habits', 'text_ms' => 'Menjejak tabiat'], ['text' => 'Social connections', 'text_ms' => 'Hubungan sosial']], 'correct_index' => 1, 'explanation' => 'BE is about becoming — defining the character traits that align with your vision.', 'explanation_ms' => 'BE adalah tentang menjadi — menentukan sifat peribadi yang sejajar dengan visi anda.'],
            ['pillar' => 'be', 'question' => 'Tawakkul means:', 'question_ms' => 'Tawakkal bermaksud:', 'options' => [['text' => 'Patience', 'text_ms' => 'Sabar'], ['text' => 'Gratitude', 'text_ms' => 'Syukur'], ['text' => 'Trust in Allah', 'text_ms' => 'Tawakkal kepada Allah'], ['text' => 'Humility', 'text_ms' => 'Tawaduk']], 'correct_index' => 2, 'explanation' => 'Tawakkul is complete trust and reliance on Allah while taking the means.', 'explanation_ms' => 'Tawakkal adalah kepercayaan dan pergantungan sepenuhnya kepada Allah sambil berusaha.'],
            ['pillar' => 'do', 'question' => 'What makes an action "aligned" in the DO framework?', 'question_ms' => 'Apakah yang menjadikan tindakan "sejajar" dalam rangka kerja DO?', 'options' => [['text' => 'It is easy to do', 'text_ms' => 'Ia mudah dilakukan'], ['text' => 'It connects to your vision and traits', 'text_ms' => 'Ia berhubung dengan visi dan sifat anda'], ['text' => 'Others are doing it', 'text_ms' => 'Orang lain melakukannya'], ['text' => 'It is popular', 'text_ms' => 'Ia popular']], 'correct_index' => 1, 'explanation' => 'Aligned actions are those that connect back to your vision (SEE) and identity (BE).', 'explanation_ms' => 'Tindakan sejajar adalah yang berhubung kembali dengan visi (SEE) dan identiti (BE) anda.'],
            ['pillar' => 'do', 'question' => 'How should you handle a missed action?', 'question_ms' => 'Bagaimana anda harus menangani tindakan yang terlepas?', 'options' => [['text' => 'Delete it', 'text_ms' => 'Padamkannya'], ['text' => 'Ignore it', 'text_ms' => 'Abaikannya'], ['text' => 'Reflect and adjust', 'text_ms' => 'Refleksi dan sesuaikan'], ['text' => 'Feel guilty', 'text_ms' => 'Rasa bersalah']], 'correct_index' => 2, 'explanation' => 'Reflection turns missed actions into learning opportunities, not guilt.', 'explanation_ms' => 'Refleksi menjadikan tindakan yang terlepas sebagai peluang pembelajaran, bukan rasa bersalah.'],
        ];

        foreach ($quizzes as $quiz) {
            Quiz::create($quiz);
        }
    }
}
