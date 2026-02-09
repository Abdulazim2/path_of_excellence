<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Student only
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = Auth::user();

        // Check if already purchased
        if ($course->isPurchasedBy($user)) {
            return response()->json(['message' => 'You have already purchased this course.'], 400);
        }

        // Check Wallet Balance
        if ($user->wallet_balance < $course->price) {
            return response()->json(['message' => 'Insufficient funds. Please recharge your wallet.'], 400);
        }

        // Deduct Funds
        $user->wallet_balance -= $course->price;
        $user->save();
        
        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'price' => $course->price,
            'payment_status' => 'paid',
        ]);

        return response()->json([
            'message' => 'Course purchased successfully',
            'order' => $order,
            'new_balance' => $user->wallet_balance
        ], 201);
    }

    public function index()
    {
        // Student only - view purchased courses
        $orders = Order::with('course')
                    ->where('user_id', Auth::id())
                    ->get();
                    
        return response()->json($orders);
    }
}
