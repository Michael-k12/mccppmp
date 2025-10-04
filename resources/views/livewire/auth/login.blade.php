<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public string $recaptcha = '';

    public bool $showCaptcha = false;

    public int $countdown = 0;

    public function mount(): void
    {
        $this->updateCaptchaStatus();
    }

    protected function updateCaptchaStatus(): void
    {
        $attempts = Session::get('login_attempts_' . $this->throttleKey(), 0);
        $this->showCaptcha = $attempts >= 3;
    }

    public function login(): void
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];

        if ($this->showCaptcha && $this->countdown <= 0) {
            $rules['recaptcha'] = 'required|recaptcha';
        }

        $this->validate($rules);

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            $failedAttempts = Session::increment('login_attempts_' . $this->throttleKey());

            if ($failedAttempts >= 3 && !$this->showCaptcha) {
                // Start 20-second countdown
                $this->countdown = 20;
                $this->dispatchBrowserEvent('start-countdown', ['seconds' => 20]);
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Login success
        RateLimiter::clear($this->throttleKey());
        Session::forget('login_attempts_' . $this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(route('dashboard', absolute: false), true);
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

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
};
    ?>

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

        <div id="recaptcha-wrapper" style="display: none;" class="mt-4">
    <div wire:ignore.self id="recaptcha-container" class="g-recaptcha"
         data-sitekey="{{ config('recaptcha.site_key') }}"
         data-callback="setRecaptchaValue">
    </div>

    <div id="recaptcha-countdown" class="text-sm text-zinc-600 mt-2"></div>

    @error('recaptcha')
        <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
    @enderror
</div>

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Log in') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

{{-- Load reCAPTCHA script --}}
@once
<script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit" async defer></script>
@endonce


<script>
function setRecaptchaValue(response) {
    @this.set('recaptcha', response);
}

function resetRecaptchaWidget() {
    if (typeof grecaptcha !== 'undefined') {
        grecaptcha.reset();
    }
}

function renderRecaptcha() {
    const container = document.getElementById('recaptcha-container');
    if (container && typeof grecaptcha !== 'undefined' && !container.hasChildNodes()) {
        grecaptcha.render(container, {
            sitekey: '{{ config('recaptcha.site_key') }}',
            callback: setRecaptchaValue
        });
    }
}

Livewire.on('start-countdown', ({seconds}) => {
    const wrapper = document.getElementById('recaptcha-wrapper');
    const countdownEl = document.getElementById('recaptcha-countdown');
    wrapper.style.display = 'block';

    let time = seconds;
    countdownEl.innerText = `Please wait ${time} seconds...`;

    const interval = setInterval(() => {
        time--;
        countdownEl.innerText = `Please wait ${time} seconds...`;

        if (time <= 0) {
            clearInterval(interval);
            countdownEl.innerText = '';
            renderRecaptcha();
        }
    }, 1000);
});
</script>