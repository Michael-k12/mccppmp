<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Validation\ValidationException;

new #[Layout('components.layouts.auth')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public int $attemptsLeft = 3;
    public ?int $lockoutSeconds = null;
    public string $g_recaptcha_response = '';

    public function mount(): void
    {
        $this->updateAttempts();
    }

    public function login(): void
    {
        $this->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'g_recaptcha_response' => 'required|captcha', // make sure recaptcha package installed
        ]);

        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->lockoutSeconds = RateLimiter::availableIn($key);
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60); // lockout for 60 sec
            $this->updateAttempts();
            return;
        }

        RateLimiter::clear($key);
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email).'|'.request()->ip();
    }

    public function updateAttempts(): void
    {
        $key = $this->throttleKey();
        $attempts = RateLimiter::attempts($key);
        $this->attemptsLeft = max(0, 3 - $attempts);

        if ($this->attemptsLeft === 0) {
            $this->lockoutSeconds = RateLimiter::availableIn($key);
        } else {
            $this->lockoutSeconds = null;
        }
    }

    public function tick(): void
    {
        if ($this->lockoutSeconds && $this->lockoutSeconds > 0) {
            $this->lockoutSeconds--;
        }

        if ($this->lockoutSeconds === 0) {
            $this->updateAttempts();
        }
    }
};
?>
<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below')" />

    <form wire:submit.prevent="login" wire:poll.1000ms="tick" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            placeholder="email@example.com"
        />

        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            placeholder="Password"
            viewable
        />

        <!-- Google reCAPTCHA -->
        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>

        @if($lockoutSeconds)
            <p class="text-red-500">
                Too many attempts. Try again in <span>{{ $lockoutSeconds }}</span> seconds.
            </p>
        @endif

        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <flux:button variant="primary" type="submit" class="w-full" @if($lockoutSeconds) disabled @endif>
            {{ __('Log in') }}
        </flux:button>
    </form>

    @if(Route::has('register'))
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
