<?php

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
    public string $captchaInput = '';
    public bool $requireCaptcha = false;

    /**
     * Handle login process.
     */
    public function login(): void
    {
        $this->validate();

        $this->checkLoginAttempts();

        // If CAPTCHA is required, validate it first
        if ($this->requireCaptcha && ! $this->validateCaptcha()) {
            throw ValidationException::withMessages([
                'captchaInput' => 'Invalid CAPTCHA. Please try again.',
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 600); // store failed attempt for 10 mins

            // Enable CAPTCHA after 3 failed attempts
            if (RateLimiter::attempts($this->throttleKey()) >= 3) {
                $this->requireCaptcha = true;
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Success — clear attempts and CAPTCHA state
        RateLimiter::clear($this->throttleKey());
        $this->requireCaptcha = false;
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Check if user exceeded attempt limit.
     */
    protected function checkLoginAttempts(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many failed attempts. Please wait before trying again.',
            ]);
        }
    }

    /**
     * Validate CAPTCHA input.
     */
    protected function validateCaptcha(): bool
    {
        // For simplicity, use a static CAPTCHA. Replace this with Google reCAPTCHA if needed.
        return strtolower(trim($this->captchaInput)) === strtolower(Session::get('captcha_text'));
    }

    /**
     * Generate a unique key for tracking attempts.
     */
    protected function throttleKey(): string
    {
        return Str::lower($this->email).'|'.request()->ip();
    }

    /**
     * Generate CAPTCHA text and store it in session.
     */
    public function generateCaptcha(): void
    {
        $text = Str::upper(Str::random(5));
        Session::put('captcha_text', $text);
    }

    public function mount()
    {
        $this->generateCaptcha();
    }
};
?>

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

        <!-- CAPTCHA Section -->
        @if ($requireCaptcha)
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-4">
                    <div class="bg-gray-100 dark:bg-zinc-800 px-4 py-2 rounded font-mono tracking-widest select-none">
                        {{ session('captcha_text') }}
                    </div>
                    <button type="button" wire:click="generateCaptcha" class="text-sm text-blue-600 hover:underline">
                        {{ __('Reload CAPTCHA') }}
                    </button>
                </div>

                <flux:input
                    wire:model="captchaInput"
                    :label="__('Enter CAPTCHA')"
                    type="text"
                    required
                    placeholder="Type the text above"
                />
            </div>
        @endif

        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    
</div>
