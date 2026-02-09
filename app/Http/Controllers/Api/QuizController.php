<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    // Teacher/Admin: Create Quiz
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        $course = Course::findOrFail($request->course_id);

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::create($request->all());

        return response()->json(['message' => 'Quiz created', 'quiz' => $quiz], 201);
    }

    // Teacher/Admin: Update Quiz
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $course = $quiz->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        $quiz->update($request->all());

        return response()->json(['message' => 'Quiz updated', 'quiz' => $quiz]);
    }

    // Teacher/Admin: Delete Quiz
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $course = $quiz->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted']);
    }

    // Teacher/Admin: Add Question
    public function storeQuestion(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $course = $quiz->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:mcq,text',
            'options' => 'required_if:type,mcq|array|min:2',
            'correct_answer' => 'required|string',
            'points' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
        ]);

        $question = $quiz->questions()->create($request->all());

        return response()->json(['message' => 'Question added', 'question' => $question], 201);
    }

    // Teacher/Admin: Update Question
    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $course = $question->quiz->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'question_text' => 'sometimes|string',
            'type' => 'sometimes|in:mcq,text',
            'options' => 'nullable|array|min:2',
            'correct_answer' => 'sometimes|string',
            'points' => 'sometimes|integer|min:1',
            'explanation' => 'nullable|string',
        ]);

        $question->update($request->all());

        return response()->json(['message' => 'Question updated', 'question' => $question]);
    }

    // Teacher/Admin: Delete Question
    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
        $course = $question->quiz->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted']);
    }

    // Student: Get Quiz (if purchased)
    public function show($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $course = $quiz->course;

        // Check if user is teacher/admin OR student who bought the course
        $user = Auth::user();
        $isOwner = $course->teacher_id === $user->id;
        $isAdmin = $user->isAdmin();
        $hasPurchased = $course->isPurchasedBy($user);

        if (!$isOwner && !$isAdmin && !$hasPurchased) {
            return response()->json(['message' => 'Unauthorized. Purchase course first.'], 403);
        }

        return response()->json($quiz);
    }

    // Student: Submit Quiz
    public function submit(Request $request, $id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $course = $quiz->course;
        
        if (!$course->isPurchasedBy(Auth::user())) {
             return response()->json(['message' => 'Unauthorized. Purchase course first.'], 403);
        }

        // Check timing
        $now = now();
        if ($quiz->start_time && $now->lt($quiz->start_time)) {
            return response()->json(['message' => 'Quiz has not started yet.'], 403);
        }
        if ($quiz->end_time && $now->gt($quiz->end_time)) {
            return response()->json(['message' => 'Quiz has ended.'], 403);
        }

        $request->validate([
            'answers' => 'required|array', // ['question_id' => 'answer_value']
        ]);

        $score = 0;
        $totalPoints = 0;
        $results = [];

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $userAnswer = $request->answers[$question->id] ?? null;
            $isCorrect = false;

            if ($question->type === 'text') {
                if (strcasecmp(trim((string)$userAnswer), trim($question->correct_answer)) === 0) {
                    $isCorrect = true;
                }
            } else {
                if ((string)$userAnswer === (string)$question->correct_answer) {
                    $isCorrect = true;
                }
            }

            if ($isCorrect) {
                $score += $question->points;
            }

            $results[] = [
                'question_id' => $question->id,
                'correct' => $isCorrect,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation,
            ];
        }

        // Pass if > 50%
        $passed = $totalPoints > 0 ? ($score / $totalPoints) >= 0.5 : false;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'score' => $score,
            'total_points' => $totalPoints,
            'passed' => $passed,
        ]);

        return response()->json([
            'message' => 'Quiz submitted',
            'score' => $score,
            'total_points' => $totalPoints,
            'passed' => $passed,
            'attempt' => $attempt,
            'results' => $results
        ]);
    }
}
