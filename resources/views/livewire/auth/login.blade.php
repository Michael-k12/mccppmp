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

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public int $attemptsLeft = 3;
    public ?int $secondsRemaining = null;
    public string $g_recaptcha_response = '';

    public function mount(): void
    {
        // If previously locked out, set secondsRemaining
        $key = $this->throttleKey();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->secondsRemaining = RateLimiter::availableIn($key);
        }
    }

    public function login(): void
    {
        $this->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'g_recaptcha_response' => 'required|captcha',
        ]);

        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->secondsRemaining = RateLimiter::availableIn($key);
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->attemptsLeft = 3 - RateLimiter::attempts($key);

            if ($this->attemptsLeft === 0) {
                $this->secondsRemaining = RateLimiter::availableIn($key);
                $this->startCountdown();
            }

            $this->dispatchBrowserEvent('login-failed', ['attemptsLeft' => $this->attemptsLeft]);
            return;
        }

        RateLimiter::clear($key);
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function startCountdown(): void
    {
        $this->dispatchBrowserEvent('start-countdown', ['seconds' => $this->secondsRemaining]);
    }
};
?>
<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
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

        <!-- Countdown -->
        @if($secondsRemaining)
            <p class="text-red-500">
                Too many attempts. Try again in <span id="countdown">{{ $secondsRemaining }}</span> seconds.
            </p>
        @endif

        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <flux:button variant="primary" type="submit" class="w-full" :disabled="$secondsRemaining > 0">
            {{ __('Log in') }}
        </flux:button>
    </form>

    @if(Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    let countdownInterval;

    Livewire.on('start-countdown', event => {
        let countdownEl = document.getElementById('countdown');
        let seconds = event.seconds;

        countdownInterval = setInterval(() => {
            countdownEl.innerText = seconds;
            seconds--;
            if(seconds < 0){
                clearInterval(countdownInterval);
                Livewire.emit('resetCountdown'); // optional: reset on component
            }
        }, 1000);
    });
</script>
@endpush
