<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Serve the API Client Entry Point
Route::get('/', function () {
    return view('welcome');
});

// Client-side Routes (These return the SPA views, logic is handled via JS/API)
Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::get('/admin', function () {
    return view('client.admin');
});

Route::get('/teacher', function () {
    return view('client.teacher');
});

Route::get('/student', function () {
    return view('client.student');
});
