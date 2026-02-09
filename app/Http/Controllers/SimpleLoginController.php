<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SimpleLoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return $this->dashboard();
        }
        return view('login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return $this->dashboard();
        }
        
        return redirect('/')->withErrors(['email' => 'Invalid credentials']);
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:teacher,student'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);
        
        Auth::login($user);
        return $this->dashboard();
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        return redirect('/');
    }
    
    private function dashboard()
    {
        $user = Auth::user();
        switch($user->role) {
            case 'admin': return redirect('/admin');
            case 'teacher': return redirect('/teacher');
            case 'student': return redirect('/student');
            default: return redirect('/');
        }
    }
}