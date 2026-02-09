<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = Auth::user();
        $courses = Course::where('teacher_id', $teacher->id)->count();
        $students = Order::whereHas('course', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->where('payment_status', 'paid')->count();
        
        $myCourses = Course::where('teacher_id', $teacher->id)->withCount('lessons')->get();
        
        return view('teacher.dashboard', compact('courses', 'students', 'myCourses'));
    }
    
    // Course Management
    public function myCourses()
    {
        $courses = Course::where('teacher_id', Auth::id())->with('lessons')->get();
        return view('teacher.courses', compact('courses'));
    }
    
    public function createCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);
        
        Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'teacher_id' => Auth::id(),
        ]);
        
        return redirect()->back()->with('success', 'Course created successfully!');
    }
    
    public function editCourse(Course $course)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        return view('teacher.edit-course', compact('course'));
    }
    
    public function updateCourse(Request $request, Course $course)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);
        
        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
        ]);
        
        return redirect()->route('teacher.courses')->with('success', 'Course updated successfully!');
    }
    
    public function deleteCourse(Course $course)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $course->delete();
        return redirect()->back()->with('success', 'Course deleted successfully!');
    }
    
    // Lesson Management
    public function courseLessons(Course $course)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $lessons = Lesson::where('course_id', $course->id)->get();
        return view('teacher.lessons', compact('course', 'lessons'));
    }
    
    public function createLesson(Request $request, Course $course)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
        ]);
        
        Lesson::create([
            'title' => $request->title,
            'video_url' => $request->video_url,
            'description' => $request->description,
            'course_id' => $course->id,
        ]);
        
        return redirect()->back()->with('success', 'Lesson created successfully!');
    }
    
    public function editLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        return view('teacher.edit-lesson', compact('lesson', 'course'));
    }
    
    public function updateLesson(Request $request, Lesson $lesson)
    {
        $course = $lesson->course;
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
        ]);
        
        $lesson->update([
            'title' => $request->title,
            'video_url' => $request->video_url,
            'description' => $request->description,
        ]);
        
        return redirect()->route('teacher.course.lessons', $course->id)->with('success', 'Lesson updated successfully!');
    }
    
    public function deleteLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $lesson->delete();
        return redirect()->back()->with('success', 'Lesson deleted successfully!');
    }
    
    // Student Management
    public function courseStudents(Course $course)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $students = Order::where('course_id', $course->id)
                         ->where('payment_status', 'paid')
                         ->with('user')
                         ->get();
        
        return view('teacher.students', compact('course', 'students'));
    }
    
    // Grade Management
    public function studentGrades(Course $course, $studentId)
    {
        if ($course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }
        
        $student = Order::where('course_id', $course->id)
                       ->where('user_id', $studentId)
                       ->where('payment_status', 'paid')
                       ->with('user')
                       ->first();
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found!');
        }
        
        // Here you would implement grade management logic
        // For now, returning a simple view
        return view('teacher.grades', compact('course', 'student'));
    }
}