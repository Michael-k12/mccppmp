<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
    public bool $showRecaptcha = false;
    public int $remainingSeconds = 0;
    public ?string $recaptchaResponse = null;

    // 🔁 Automatically called every second to update countdown
    public function tick(): void
    {
        if ($this->remainingSeconds > 0) {
            $this->remainingSeconds--;

            if ($this->remainingSeconds === 0) {
                $this->showRecaptcha = true;
            }
        }
    }

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if ($this->showRecaptcha) {
            $this->validateRecaptcha();
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            if (RateLimiter::attempts($this->throttleKey()) >= 3) {
                $this->startCountdown();
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) return;

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function validateRecaptcha(): void
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $this->recaptchaResponse,
            'remoteip' => request()->ip(),
        ]);

        if (!$response->json('success')) {
            throw ValidationException::withMessages([
                'recaptcha' => 'Please verify that you are not a robot.',
            ]);
        }
    }

    public function startCountdown(): void
    {
        $this->remainingSeconds = 20;
        $this->showRecaptcha = false;
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
};
?>

<!-- 🔁 wire:poll.1s makes countdown live -->
<div class="flex flex-col gap-6" wire:poll.1s="tick">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
        <flux:input wire:model="email" label="Email address" type="email" required />
        <flux:input wire:model="password" label="Password" type="password" required />
        <flux:checkbox wire:model="remember" label="Remember me" />

        <!-- Live countdown -->
        @if ($remainingSeconds > 0)
            <div class="text-center text-red-500">
                Please wait <b>{{ $remainingSeconds }}</b> seconds before next attempt.
            </div>
        @endif

        <!-- reCAPTCHA appears after countdown -->
        @if ($showRecaptcha)
            <div class="flex justify-center mt-4" wire:ignore>
                <div class="g-recaptcha"
                     data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"
                     x-data
                     x-init="
                        window.recaptchaCallback = (response) => {
                            $wire.set('recaptchaResponse', response);
                        };
                     ">
                </div>
            </div>
        @endif

        <flux:button variant="primary" type="submit" class="w-full">
            Log in
        </flux:button>
    </form>
</div>

<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
