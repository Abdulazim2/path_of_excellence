<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    public function showLoginForm()
    {
        // Check if user is already logged in
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }
        
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    
    public function showRegisterForm()
    {
        // Check if user is already logged in
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:teacher,student',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        
        Auth::login($user);
        $request->session()->regenerate();
        
        return $this->redirectBasedOnRole($user);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('message', 'You have been logged out successfully.');
    }
    
    private function redirectBasedOnRole($user)
    {
        switch($user->role) {
            case 'admin':
                return redirect('/admin');
            case 'teacher':
                return redirect('/teacher');
            case 'student':
                return redirect('/student');
            default:
                return redirect('/');
        }
    }
}