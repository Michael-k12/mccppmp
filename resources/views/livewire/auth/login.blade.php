<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    // New property to hold the recaptcha response
    public string $recaptcha = ''; 

    // New property to control captcha visibility
    public bool $showCaptcha = false;

    /**
     * Mount the component to check if captcha should be shown.
     */
    public function mount(): void
    {
        $this->checkIfCaptchaNeeded();
    }

    /**
     * Check failed attempts and set $showCaptcha.
     */
    protected function checkIfCaptchaNeeded(): void
    {
        // Use a simple session counter for the failed attempts for this email/IP
        $attempts = Session::get('login_attempts_' . $this->throttleKey(), 0);
        $this->showCaptcha = $attempts >= 3;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        // Add recaptcha to validation rules if needed
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];

        if ($this->showCaptcha) {
            // Note: This validation rule assumes you are using the anhskohbo/no-captcha package.
            // If you use a different package, change 'recaptcha' to the correct rule.
            $rules['recaptcha'] = 'required|recaptcha'; 
        }

        $this->validate($rules);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            // Increment failed attempts counter and check if captcha should be shown
            $failedAttempts = Session::increment('login_attempts_' . $this->throttleKey());
            if ($failedAttempts >= 3) {
                $this->showCaptcha = true; // Show captcha on the next render
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Authentication successful
        RateLimiter::clear($this->throttleKey());
        Session::forget('login_attempts_' . $this->throttleKey()); // Clear failed attempts
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited. (Unchanged)
     */
    protected function ensureIsNotRateLimited(): void
    {
        // ... (existing code for rate limiting)
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key. (Unchanged)
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />
        </div>

        <flux:checkbox wire:model="remember" :label="__('Remember me')" />
        
        @if ($showCaptcha)
            <div class="mt-4">
                <div 
                    wire:ignore
                    class="g-recaptcha" 
                    data-sitekey="{{ config('recaptcha.site_key') }}"
                    data-callback="setRecaptchaValue"
                ></div>
                
                @error('recaptcha')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>

            @once
                {{-- Script to load reCAPTCHA and set its value on the Livewire component --}}
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                <script>
                    function setRecaptchaValue(response) {
                        // This uses Livewire's internal mechanism to set the value
                        @this.set('recaptcha', response);
                    }
                </script>
            @endonce
        @endif

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>