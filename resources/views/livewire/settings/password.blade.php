<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    // ✅ Custom Password Rule:
                    // Must start uppercase, follow with lowercase & numbers only,
                    // No symbols, minimum 12 chars.
                    'regex:/^(?=.{12,}$)(?=.*[a-z])(?=.*\d)[A-Z][a-z\d]+$/',
                ],
            ], [
                // ✅ Custom error message to guide user clearly
                'password.regex' => 'Password must start with an uppercase letter, contain lowercase letters and numbers only, no symbols, and be at least 12 characters.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            // ✅ Argon2id hashing will apply automatically
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a strong and secure password')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
            />

            <p class="text-sm text-gray-600">
                <strong>Password Requirements:</strong><br>
                - Must start with an <strong>UPPERCASE</strong> letter<br>
                - Contains <strong>lowercase</strong> letters and <strong>numbers</strong> only<br>
                - <strong>No symbols allowed</strong><br>
                - Minimum length: <strong>12 characters</strong>
            </p>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
