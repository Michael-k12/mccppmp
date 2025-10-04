@php
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public string $recaptcha = '';
    public int $remainingSeconds = 0;
    protected int $maxAttempts = 3;
    protected int $lockoutSeconds = 20;

    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();
        $this->verifyRecaptcha();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), $this->lockoutSeconds);
            $this->remainingSeconds = $this->lockoutSeconds;

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
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $this->remainingSeconds = $seconds;

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

    protected function verifyRecaptcha(): void
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $this->recaptcha,
            'remoteip' => request()->ip(),
        ]);

        if (! $response->json('success')) {
            throw ValidationException::withMessages([
                'recaptcha' => __('Invalid reCAPTCHA verification.'),
            ]);
        }
    }

    // Polling countdown
    public function tick(): void
    {
        if ($this->remainingSeconds > 0) {
            $this->remainingSeconds--;
        }
    }
};
@endphp

<div class="flex flex-col gap-6" wire:poll.1000ms="tick">
    <x-auth-header 
        :title="__('Log in to your account')" 
        :description="__('Enter your email and password below to log in')" 
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">

        <!-- Email -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
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
            />
        </div>

        <!-- Google reCAPTCHA -->
        <div class="my-4">
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.key') }}"></div>
            <input type="hidden" wire:model="recaptcha" id="recaptcha-response">
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <!-- Submit -->
        <div class="flex flex-col gap-2">
            @if($remainingSeconds > 0)
                <p class="text-red-500 text-center">
                    {{ __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $remainingSeconds]) }}
                </p>
                <flux:button variant="primary" type="submit" class="w-full" disabled>
                    {{ __('Log in') }}
                </flux:button>
            @else
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Log in') }}
                </flux:button>
            @endif
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('{{ config('services.recaptcha.key') }}', {action: 'login'}).then(function(token) {
            document.getElementById('recaptcha-response').value = token;
        });
    });
</script>
