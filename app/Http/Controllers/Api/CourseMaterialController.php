<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseMaterialController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $course = Course::findOrFail($request->course_id);

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = $request->file('file')->store('course_materials', 'public');

        $material = CourseMaterial::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'file_path' => $path,
            'type' => $request->file('file')->getClientOriginalExtension(),
        ]);

        return response()->json(['message' => 'File uploaded', 'material' => $material], 201);
    }

    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = Auth::user();

        // Check access
        if ($course->teacher_id !== $user->id && !$user->isAdmin() && !$course->isPurchasedBy($user)) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($course->materials);
    }

    public function destroy($id)
    {
        $material = CourseMaterial::findOrFail($id);
        $course = $material->course;

        if ($course->teacher_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($material->file_path);
        
        $material->delete();

        return response()->json(['message' => 'Material deleted successfully']);
    }
}
