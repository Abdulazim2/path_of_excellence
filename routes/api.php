<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\CourseMaterialController;

use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::apiResource('users', AdminController::class);
    });

    // Teacher & Admin Routes (Course Management)
    // Both roles can access these, logic is handled in Controllers via Admin override
    Route::middleware(['role:teacher,admin'])->group(function () {
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
        Route::get('/teacher/courses', [CourseController::class, 'myCourses']);
        Route::get('/courses/{id}/students', [CourseController::class, 'students']);
        
        // Lessons
        Route::post('/lessons', [LessonController::class, 'store']);
        Route::put('/lessons/{id}', [LessonController::class, 'update']);
        Route::delete('/lessons/{id}', [LessonController::class, 'destroy']);
        
        // Materials
        Route::post('/materials', [CourseMaterialController::class, 'store']);
        Route::delete('/materials/{id}', [CourseMaterialController::class, 'destroy']);

        // Quizzes
        Route::post('/quizzes', [QuizController::class, 'store']);
        Route::put('/quizzes/{id}', [QuizController::class, 'update']);
        Route::delete('/quizzes/{id}', [QuizController::class, 'destroy']);
        
        // Questions
        Route::post('/quizzes/{quizId}/questions', [QuizController::class, 'storeQuestion']);
        Route::put('/questions/{id}', [QuizController::class, 'updateQuestion']);
        Route::delete('/questions/{id}', [QuizController::class, 'destroyQuestion']);
    });

    // Student Routes (Purchase & Consume)
    Route::middleware(['role:student'])->group(function () {
        Route::post('/orders', [OrderController::class, 'store']); // Purchase
        Route::get('/student/orders', [OrderController::class, 'index']); // My Orders
        Route::post('/recharge', [PaymentController::class, 'recharge']); // Recharge Wallet
    });
    
    // Shared Routes (Student + Teacher + Admin) - Access Controlled in Controller
    Route::get('/lessons/{id}', [LessonController::class, 'show']);
    Route::get('/courses/{id}/materials', [CourseMaterialController::class, 'index']);
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes/{id}/submit', [QuizController::class, 'submit']);
});
