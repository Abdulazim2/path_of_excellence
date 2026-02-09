<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        // Students/Public can view all courses
        $courses = Course::with('teacher:id,name')->get();
        return response()->json($courses);
    }

    public function show($id)
    {
        $course = Course::with(['teacher:id,name', 'lessons', 'quizzes'])->findOrFail($id);

        // Check access for video_url visibility
        $user = Auth::guard('sanctum')->user();
        $canAccess = false;

        if ($user) {
            if ($user->isAdmin() || $course->teacher_id === $user->id || $course->isPurchasedBy($user)) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            $course->lessons->makeHidden('video_url');
        }

        return response()->json($course);
    }

    public function store(Request $request)
    {
        // Teacher only
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|string', // URL or path
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'thumbnail' => $request->thumbnail,
            'teacher_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        // Allow if Teacher owns it OR User is Admin
        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'thumbnail' => 'nullable|string',
        ]);

        $course->update($request->all());

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course
        ]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Allow if Teacher owns it OR User is Admin
        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function myCourses(Request $request)
    {
        // Teacher only
        $courses = Course::where('teacher_id', Auth::id())->get();
        return response()->json($courses);
    }

    public function students($id)
    {
        $course = Course::findOrFail($id);

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $orders = \App\Models\Order::where('course_id', $id)
                    ->where('payment_status', 'paid')
                    ->with('user')
                    ->get();
        
        // Get all quizzes for this course
        $quizIds = $course->quizzes()->pluck('id');

        // Transform to list of students with progress
        $students = $orders->map(function($order) use ($quizIds) {
            // Get quiz attempts for this user and this course's quizzes
            $attempts = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
                            ->where('user_id', $order->user->id)
                            ->with('quiz:id,title')
                            ->get()
                            ->map(function($attempt) {
                                return [
                                    'quiz_title' => $attempt->quiz->title,
                                    'score' => $attempt->score,
                                    'total' => $attempt->total_points,
                                    'passed' => $attempt->passed
                                ];
                            });

            return [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
                'joined_at' => $order->created_at->format('Y-m-d'),
                'grades' => $attempts
            ];
        });

        return response()->json($students);
    }
}
