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
    public ?int $cooldown = null;
    public bool $locked = false;
    public bool $showRecaptcha = false; // show reCAPTCHA after lockout

    public function mount(): void
    {
        $this->checkCooldown();
    }

    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 20); // lock for 20 seconds after 3 fails

            if (RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
                $this->locked = true;
                $this->cooldown = RateLimiter::availableIn($this->throttleKey());
                $this->showRecaptcha = true;
            }

            throw ValidationException::withMessages([
                'email' => __('Invalid email or password.'),
            ]);
        }

        if ($this->showRecaptcha) {
            $this->validateRecaptcha();
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        $this->locked = true;
        $this->cooldown = RateLimiter::availableIn($this->throttleKey());
        $this->showRecaptcha = true;

        event(new Lockout(request()));

        throw ValidationException::withMessages([
            'email' => "Too many attempts. Please wait {$this->cooldown} seconds and complete the reCAPTCHA before retrying.",
        ]);
    }

    protected function checkCooldown(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            $this->locked = true;
            $this->cooldown = RateLimiter::availableIn($this->throttleKey());
            $this->showRecaptcha = true;
        }
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }

    public function tick(): void
    {
        if ($this->cooldown !== null && $this->cooldown > 0) {
            $this->cooldown--;
            if ($this->cooldown <= 0) {
                $this->locked = false;
                RateLimiter::clear($this->throttleKey());
            }
        }
    }

    protected function validateRecaptcha(): void
    {
        $response = request('g-recaptcha-response');

        if (!$response) {
            throw ValidationException::withMessages([
                'email' => 'Please confirm you are not a robot.',
            ]);
        }

        $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . env('RECAPTCHA_SECRET_KEY') . '&response=' . $response);
        $captcha_success = json_decode($verify);

        if (!$captcha_success->success) {
            throw ValidationException::withMessages([
                'email' => 'reCAPTCHA verification failed. Please try again.',
            ]);
        }
    }
};
?>

<div class="flex flex-col gap-6" wire:poll.1s="tick">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6" id="loginForm">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
            :disabled="$locked"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
                :disabled="$locked"
            />
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" :disabled="$locked" />

        <!-- Google reCAPTCHA -->
        @if ($showRecaptcha)
            <div class="text-center mt-2">
                <div class="g-recaptcha inline-block" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
            </div>
        @endif

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full" :disabled="$locked">
                {{ $locked ? __('Please wait...') : __('Log in') }}
            </flux:button>
        </div>
    </form>

    @if ($locked)
        <div class="text-center text-sm text-red-600 dark:text-red-400">
            Too many attempts. Try again in <b>{{ $cooldown }}</b> seconds.
        </div>
    @endif

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

<!-- ✅ reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
