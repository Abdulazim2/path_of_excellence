<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    // Get Lesson: Student (Purchased) / Teacher / Admin
    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);
        $course = $lesson->course;

        $user = Auth::user();
        
        // Check access
        $canAccess = false;
        if ($user->isAdmin() || $course->teacher_id === $user->id || $course->isPurchasedBy($user)) {
            $canAccess = true;
        }

        if (!$canAccess) {
            return response()->json(['message' => 'Unauthorized. Purchase course first.'], 403);
        }

        return response()->json($lesson);
    }

    // Store: Teacher/Admin
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:51200', // 50MB max
        ]);

        if (!$request->video_url && !$request->hasFile('video_file')) {
            return response()->json(['message' => 'Please provide a video URL or upload a video file.'], 422);
        }

        $course = Course::findOrFail($request->course_id);

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this course.'], 403);
        }

        $data = $request->all();

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('lessons_videos', 'public');
            $data['video_url'] = asset('storage/' . $path);
        }

        $lesson = Lesson::create($data);

        return response()->json([
            'message' => 'Lesson added successfully',
            'lesson' => $lesson
        ], 201);
    }

    // Update: Teacher/Admin
    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $course = $lesson->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'video_url' => 'sometimes|url',
        ]);

        $lesson->update($request->all());

        return response()->json([
            'message' => 'Lesson updated successfully',
            'lesson' => $lesson
        ]);
    }

    // Delete: Teacher/Admin
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $course = $lesson->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }
}
