<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                Rules\Password::defaults(),
            ],
            'role' => ['required', 'in:principal,BSIT,BSBA,BSHM,BSED,NURSE,LIBRARY'],
        ]);

        // Hash password using Argon2id
        $validated['password'] = Hash::make($validated['password'], [
            'memory' => 1024,
            'time' => 2,
            'threads' => 2,
            'type' => PASSWORD_ARGON2ID
        ]);

        $user = User::create($validated);
        event(new Registered($user));

        // Redirect to login page
        $this->redirectRoute('login');
    }
};

?>


<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" :placeholder="__('Full name')" />


        <flux:input wire:model="email" :label="__('Email address')" type="email" required autocomplete="email" placeholder="email@example.com" />

        <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password" :placeholder="__('Password')" viewable />


        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" :placeholder="__('Confirm password')" viewable />

        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700">Select Role</label>
            <select wire:model="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Select role</option>
                <option value="principal">Principal</option>
                <option value="BSIT">BSIT</option>
                <option value="BSHM">BSHM</option>
                <option value="BSBA">BSBA</option>
                <option value="BSED">BSED</option>
                <option value="NURSE">Nurse</option>
                <option value="LIBRARY">Library</option>
            </select>
            @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate class="text-blue-600 hover:underline">{{ __('Log in') }}</flux:link>
    </div>
</div>
