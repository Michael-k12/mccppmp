<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public bool $showCaptcha = false;
    public bool $captchaReady = false;
    public int $countdown = 20; // countdown seconds
    public string $captchaToken = '';

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $attempts = RateLimiter::attempts($this->throttleKey());

        // Full lockout after 5 failed attempts
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many failed login attempts. Please wait before trying again.',
            ]);
        }

        // Enable CAPTCHA after 3 failed attempts
        if ($attempts >= 3) {
            $this->showCaptcha = true;

            if (!$this->captchaReady) {
                throw ValidationException::withMessages([
                    'email' => "Please wait for CAPTCHA to be ready after {$this->countdown} seconds.",
                ]);
            }

            if (!$this->validateRecaptcha()) {
                throw ValidationException::withMessages([
                    'email' => 'CAPTCHA verification failed.',
                ]);
            }
        }

        // Attempt authentication
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 600); // 10-minute store
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Success
        RateLimiter::clear($this->throttleKey());
        $this->showCaptcha = false;
        $this->captchaReady = false;
        Session::regenerate();

        return redirect()->intended(route('dashboard'));
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email) . '|' . request()->ip();
    }

    protected function validateRecaptcha(): bool
    {
        $token = $this->captchaToken ?: request()->input('g-recaptcha-response');
        if (!$token) return false;

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ])->json();

        return $response['success'] ?? false;
    }

    public function mount()
    {
        $this->showCaptcha = false;
        $this->captchaReady = false;
    }
}

?>

<div class="flex flex-col gap-6 max-w-md mx-auto mt-10" x-data="{ countdown: @entangle('countdown'), captchaReady: @entangle('captchaReady') }"
    x-init="
    @this.$watch('showCaptcha', value => {
        if(value){
            countdown = 20;
            captchaReady = false;
            let timer = setInterval(() => {
                if(countdown > 0){
                    countdown--;
                } else {
                    captchaReady = true;
                    clearInterval(timer);
                }
            }, 1000);
        }
    });
"

>
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
        <!-- Email -->
        <flux:input wire:model="email" type="email" required autofocus placeholder="email@example.com" />

        <!-- Password -->
        <flux:input wire:model="password" type="password" required placeholder="Password" viewable />

        <div x-data="{ showCaptcha: @entangle('showCaptcha'), countdown: @entangle('countdown'), captchaReady: @entangle('captchaReady') }">
    <template x-if="showCaptcha">
        <div class="flex flex-col gap-2">
            <template x-if="!captchaReady">
                <span class="text-red-500 font-bold">
                    Please wait <span x-text="countdown"></span> seconds before CAPTCHA.
                </span>
            </template>

            <template x-if="captchaReady">
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.key') }}" wire:model="captchaToken"></div>
            </template>
        </div>
    </template>
</div>


        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="'Remember me'" />

        <!-- Submit -->
        <flux:button type="submit" variant="primary" class="w-full">Log in</flux:button>
    </form>

    @if (Route::has('register'))
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400 mt-4">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

