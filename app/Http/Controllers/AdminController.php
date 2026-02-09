<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $users = User::count();
        $courses = Course::count();
        $lessons = Lesson::count();
        $orders = Order::count();
        
        $recentUsers = User::latest()->take(5)->get();
        $recentCourses = Course::with('teacher')->latest()->take(5)->get();
        
        return view('admin.dashboard', compact('users', 'courses', 'lessons', 'orders', 'recentUsers', 'recentCourses'));
    }
    
    // User Management
    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }
    
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,teacher,student',
        ]);
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        
        return redirect()->back()->with('success', 'User created successfully!');
    }
    
    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }
    
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,teacher,student',
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);
        
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        
        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }
    
    public function deleteUser(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully!');
    }
    
    // Course Management
    public function courses()
    {
        $courses = Course::with('teacher')->get();
        return view('admin.courses', compact('courses'));
    }
    
    public function createCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'teacher_id' => 'required|exists:users,id',
        ]);
        
        Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'teacher_id' => $request->teacher_id,
        ]);
        
        return redirect()->back()->with('success', 'Course created successfully!');
    }
    
    public function editCourse(Course $course)
    {
        $teachers = User::where('role', 'teacher')->get();
        return view('admin.edit-course', compact('course', 'teachers'));
    }
    
    public function updateCourse(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'teacher_id' => 'required|exists:users,id',
        ]);
        
        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'teacher_id' => $request->teacher_id,
        ]);
        
        return redirect()->route('admin.courses')->with('success', 'Course updated successfully!');
    }
    
    public function deleteCourse(Course $course)
    {
        $course->delete();
        return redirect()->back()->with('success', 'Course deleted successfully!');
    }
    
    // Order Management
    public function orders()
    {
        $orders = Order::with(['user', 'course'])->get();
        return view('admin.orders', compact('orders'));
    }
    
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid',
        ]);
        
        $order->update([
            'payment_status' => $request->payment_status,
        ]);
        
        return redirect()->back()->with('success', 'Order status updated successfully!');
    }
}