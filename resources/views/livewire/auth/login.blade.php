<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
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

    // OTP Properties
    public bool $showOtpForm = false;
    public string $otpCode = '';
    public string $generatedOtp = '';

    // 🔁 Tick for countdown
    public function tick(): void
    {
        if ($this->remainingSeconds > 0) {
            $this->remainingSeconds--;
        }
    }

    // Normal password login
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
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    // OTP request
    public function requestOtp(): void
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $this->generatedOtp = mt_rand(100000, 999999);

        Mail::raw("Your OTP for login is: {$this->generatedOtp}", function ($message) {
            $message->to($this->email)
                ->subject('Login OTP - Project Procurement Management System');
        });

        $this->showOtpForm = true;
        session()->flash('success', 'OTP sent to your email.');
    }

    // OTP login
    public function loginWithOtp(): void
    {
        $this->validate([
            'otpCode' => 'required|digits:6',
        ]);

        if ($this->otpCode != $this->generatedOtp) {
            $this->addError('otpCode', 'Invalid OTP.');
            return;
        }

        $user = \App\Models\User::where('email', $this->email)->first();
        Auth::login($user);

        // Clear OTP
        $this->generatedOtp = '';
        $this->otpCode = '';
        $this->showOtpForm = false;

        $this->redirect(route('dashboard'));
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

    <!-- Normal Password Login -->
    <form wire:submit.prevent="login" class="flex flex-col gap-4">
        <flux:input wire:model="email" label="Email address" type="email" required />
        <flux:input wire:model="password" label="Password" type="password" required />
        <flux:checkbox wire:model="remember" label="Remember me" />

        <div class="text-right text-sm">
            <button type="button" wire:click="requestOtp" class="text-blue-600 hover:underline">
                Forgot Password?
            </button>
        </div>

        @if ($remainingSeconds > 0)
            <div class="text-center text-red-500">
                Please wait <b>{{ $remainingSeconds }}</b> seconds before next attempt.
            </div>
        @endif

        <flux:button variant="primary" type="submit" class="w-full">
            Log in
        </flux:button>
    </form>

    <!-- OTP Login -->
    @if ($showOtpForm)
        <div class="mt-6 p-4 border rounded-lg bg-gray-50">
            <p class="text-sm text-gray-700 mb-2">Enter the OTP sent to your email:</p>
            <form wire:submit.prevent="loginWithOtp" class="flex flex-col gap-4">
                <flux:input wire:model="otpCode" label="OTP" type="text" maxlength="6" required />
                <flux:button variant="primary" type="submit" class="w-full">
                    Login with OTP
                </flux:button>
            </form>
        </div>
    @endif

</div>

@if (session()->has('success'))
    <script>
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    </script>
@endif
