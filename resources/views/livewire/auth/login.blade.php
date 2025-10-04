<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public string $recaptcha = '';
    public bool $showCaptcha = false;

    public function mount(): void
    {
        $this->checkIfCaptchaNeeded();
    }

    protected function checkIfCaptchaNeeded(): void
    {
        $attempts = Session::get('login_attempts_' . $this->throttleKey(), 0);
        $this->showCaptcha = $attempts >= 3;
    }

    public function login(): void
    {
        // Pre-check failed attempts to show captcha
        $failedAttempts = Session::get('login_attempts_' . $this->throttleKey(), 0);
        if ($failedAttempts >= 3) {
            $this->showCaptcha = true;
        }

        // Validation rules
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];

        if ($this->showCaptcha) {
            $rules['recaptcha'] = 'required|recaptcha';
        }

        $this->validate($rules);

        $this->ensureIsNotRateLimited();

        // Attempt authentication
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {

            // Increment failed attempts
            $failedAttempts = Session::increment('login_attempts_' . $this->throttleKey());

            // Show captcha if attempts >= 3
            if ($failedAttempts >= 3 && ! $this->showCaptcha) {
                $this->showCaptcha = true;
            }

            // Reset captcha widget if already visible
            if ($this->showCaptcha) {
                $this->dispatchBrowserEvent('resetRecaptcha');
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Successful login
        RateLimiter::clear($this->throttleKey());
        Session::forget('login_attempts_' . $this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(route('dashboard'));
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) return;

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }


}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Log in to your account')" 
        :description="__('Enter your email and password below to log in')" 
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
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
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                <script>
                    function setRecaptchaValue(response) {
                        @this.set('recaptcha', response);
                    }

                    window.addEventListener('resetRecaptcha', () => {
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.reset();
                        }
                    });
                </script>
            @endonce
        @endif

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Log in') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __("Don't have an account?") }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
