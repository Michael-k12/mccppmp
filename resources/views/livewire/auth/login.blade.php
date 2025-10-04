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

new #[Layout('components.layouts.auth')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public int $remainingSeconds = 0;

    // 🔁 Automatically called every second to update countdown
    public function tick(): void
    {
        if ($this->remainingSeconds > 0) {
            $this->remainingSeconds--;
        }
    }

    public function login(): void
{
    $this->validate();
    $this->ensureIsNotRateLimited();

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

    // ✅ No Session::regenerate() here — handled internally by Laravel/Livewire
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

    public function startCountdown(): void
    {
        $this->remainingSeconds = 20;
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}

?>
<div class="flex flex-col gap-6" wire:poll.1s="tick">
    <x-auth-header 
        :title="__('Log in to your account')" 
        :description="__('Enter your email and password below to log in')" 
    />

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

        <flux:button variant="primary" type="submit" class="w-full">
            Log in
        </flux:button>
    </form>
</div>
