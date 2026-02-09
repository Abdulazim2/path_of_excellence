<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        // List all users
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        // Create a new user (Teacher/Student/Admin)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,student',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'sometimes|in:admin,teacher,student',
            'password' => 'sometimes|string|min:8|confirmed',
            'wallet_balance' => 'sometimes|numeric|min:0', // Admin can set balance directly
            'add_funds' => 'sometimes|numeric|min:0', // Or add to existing
        ]);

        $data = $request->except(['password', 'add_funds']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->has('add_funds')) {
            $user->wallet_balance += $request->add_funds;
            $user->save();
        }

        $user->update($data);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
