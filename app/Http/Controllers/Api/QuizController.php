<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        return QuizResource::collection(Quiz::all());
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*.quiz_id' => 'required|exists:quizzes,id',
            'answers.*.selected' => 'required|integer',
        ]);

        $score = 0;
        $results = [];
        foreach ($data['answers'] as $answer) {
            $quiz = Quiz::find($answer['quiz_id']);
            $correct = $quiz->correct_index === $answer['selected'];
            if ($correct) $score++;
            $results[] = ['quiz_id' => $answer['quiz_id'], 'selected' => $answer['selected'], 'correct' => $correct];
        }

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'score' => $score,
            'total' => count($data['answers']),
            'answers' => $results,
            'created_at' => now(),
        ]);

        return response()->json(['score' => $score, 'total' => count($data['answers']), 'results' => $results]);
    }

    public function history(Request $request)
    {
        return response()->json(['data' => $request->user()->quizAttempts()->latest('created_at')->get()]);
    }
}
