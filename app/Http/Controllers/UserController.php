<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create'); // create.blade.php
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'string',
            'min:12',              // Minimum 12 characters
            'regex:/[a-z]/',       // At least one lowercase
            'regex:/[A-Z]/',       // At least one uppercase
            'regex:/[0-9]/',       // At least one number
            'regex:/[@$!%*?&#]/',  // At least one special character
            'confirmed',           // Must match password_confirmation
        ],
        'role' => 'required|string',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password, [
            'memory' => 1024, 
            'time' => 2, 
            'threads' => 2, 
            'type' => PASSWORD_ARGON2ID
        ]),
        'role' => $request->role,
    ]);

    return redirect()->route('users.index')->with('success', 'User added successfully.');
}

public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => [
            'nullable',
            'string',
            'min:12',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[@$!%*?&#]/',
            'confirmed',
        ],
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password, [
            'memory' => 1024,
            'time' => 2,
            'threads' => 2,
            'type' => PASSWORD_ARGON2ID
        ]);
    }

    $user->save();

    return redirect()->route('users.index')->with('success', 'User updated successfully.');
}


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
