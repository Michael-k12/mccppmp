<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required', 'string', 'min:12',
            'regex:/[a-z]/', 'regex:/[A-Z]/',
            'regex:/[0-9]/', 'regex:/[@$!%*?&#]/',
            'confirmed',
        ],
        'role' => 'required|string',
    ]);

    $plainPassword = $request->password;

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($plainPassword),
        'role' => $request->role,
    ]);

    // ✅ Send Gmail notification
    Mail::to($user->email)->send(new UserAccountCreatedMail($user, $plainPassword));

    return redirect()->route('users.index')->with('success', 'User added successfully and email sent!');
}

    // ✅ Add this edit method
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => [
                'nullable', 'string', 'min:12',
                'regex:/[a-z]/', 'regex:/[A-Z]/',
                'regex:/[0-9]/', 'regex:/[@$!%*?&#]/',
                'confirmed',
            ],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
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
