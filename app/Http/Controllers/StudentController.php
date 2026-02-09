<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = Auth::user();
        $enrolledCourses = Order::where('user_id', $student->id)
                               ->where('payment_status', 'paid')
                               ->with('course')
                               ->get();
        
        $availableCourses = Course::whereDoesntHave('orders', function($query) use ($student) {
            $query->where('user_id', $student->id);
        })->get();
        
        return view('student.dashboard', compact('enrolledCourses', 'availableCourses'));
    }
    
    // Browse Courses
    public function browseCourses()
    {
        $courses = Course::with('teacher')->get();
        return view('student.browse', compact('courses'));
    }
    
    // Course Details
    public function courseDetails(Course $course)
    {
        $isEnrolled = Order::where('user_id', Auth::id())
                          ->where('course_id', $course->id)
                          ->where('payment_status', 'paid')
                          ->exists();
        
        $lessons = Lesson::where('course_id', $course->id)->get();
        
        return view('student.course-details', compact('course', 'isEnrolled', 'lessons'));
    }
    
    // Enroll in Course
    public function enrollCourse(Course $course)
    {
        // Check if already enrolled
        $existingOrder = Order::where('user_id', Auth::id())
                             ->where('course_id', $course->id)
                             ->first();
        
        if ($existingOrder) {
            if ($existingOrder->payment_status === 'paid') {
                return redirect()->back()->with('error', 'You are already enrolled in this course!');
            } else {
                // Update existing pending order
                $existingOrder->update([
                    'price' => $course->price,
                    'payment_status' => 'paid'
                ]);
            }
        } else {
            // Create new order
            Order::create([
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'price' => $course->price,
                'payment_status' => 'paid'
            ]);
        }
        
        return redirect()->back()->with('success', 'Successfully enrolled in the course!');
    }
    
    // My Courses
    public function myCourses()
    {
        $courses = Order::where('user_id', Auth::id())
                       ->where('payment_status', 'paid')
                       ->with('course')
                       ->get();
        
        return view('student.my-courses', compact('courses'));
    }
    
    // View Course Content
    public function viewCourse(Course $course)
    {
        // Check if student is enrolled
        $isEnrolled = Order::where('user_id', Auth::id())
                          ->where('course_id', $course->id)
                          ->where('payment_status', 'paid')
                          ->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('student.dashboard')->with('error', 'You are not enrolled in this course!');
        }
        
        $lessons = Lesson::where('course_id', $course->id)->get();
        
        return view('student.view-course', compact('course', 'lessons'));
    }
    
    // View Lesson
    public function viewLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        
        // Check if student is enrolled in the course
        $isEnrolled = Order::where('user_id', Auth::id())
                          ->where('course_id', $course->id)
                          ->where('payment_status', 'paid')
                          ->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('student.dashboard')->with('error', 'You are not enrolled in this course!');
        }
        
        $lessons = Lesson::where('course_id', $course->id)->get();
        
        return view('student.view-lesson', compact('lesson', 'course', 'lessons'));
    }
    
    // Progress Tracking
    public function trackProgress()
    {
        $courses = Order::where('user_id', Auth::id())
                       ->where('payment_status', 'paid')
                       ->with('course.lessons')
                       ->get();
        
        return view('student.progress', compact('courses'));
    }
}