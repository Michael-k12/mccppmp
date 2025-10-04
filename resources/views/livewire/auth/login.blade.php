<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Mail\LoginOtpMail;

new #[Layout('components.layouts.auth')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public int $remainingSeconds = 0;

    public bool $showOtpForm = false;
    public string $otpCode = '';

    public int $otpExpireMinutes = 5;
    public int $otpMaxAttempts = 5;
    public int $resendCooldown = 0;
    public int $resendSeconds = 30;

    // 🔁 Tick for countdown
    public function tick(): void
    {
        if ($this->remainingSeconds > 0) $this->remainingSeconds--;
        if ($this->resendCooldown > 0) $this->resendCooldown--;
    }

    // Password login
    public function login(): void
    {
        $this->validate();
        $this->ensureRateLimit();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 60);
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $this->sendOtp(); // Trigger OTP
    }

    // Generate OTP and send email
    public function sendOtp(): void
    {
        $cacheKey = $this->otpCacheKey();

        if ($this->resendCooldown > 0) {
            $this->addError('otpCode', "Wait {$this->resendCooldown}s before resending OTP.");
            return;
        }

        $otp = random_int(100000, 999999);

        Cache::put($cacheKey, [
            'otp' => encrypt($otp),
            'attempts' => 0,
        ], now()->addMinutes($this->otpExpireMinutes));

        Mail::to($this->email)->send(new LoginOtpMail($otp));

        $this->showOtpForm = true;
        $this->resendCooldown = $this->resendSeconds;
        session()->flash('success', "OTP sent to your email. Valid for {$this->otpExpireMinutes} minutes.");
    }

    // OTP login
    public function loginWithOtp(): void
    {
        $this->validate(['otpCode' => 'required|digits:6']);
        $cacheKey = $this->otpCacheKey();
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            $this->addError('otpCode', 'OTP expired. Request a new one.');
            return;
        }

        if ($otpData['attempts'] >= $this->otpMaxAttempts) {
            Cache::forget($cacheKey);
            $this->addError('otpCode', 'Maximum OTP attempts exceeded.');
            return;
        }

        if (!hash_equals($this->otpCode, decrypt($otpData['otp']))) {
            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addMinutes($this->otpExpireMinutes));
            $this->addError('otpCode', 'Invalid OTP.');
            return;
        }

        $user = \App\Models\User::where('email', $this->email)->first();
        Auth::login($user, $this->remember);
        session()->regenerate();

        Cache::forget($cacheKey);
        $this->showOtpForm = false;
        $this->redirect(route('dashboard'));
    }

    protected function otpCacheKey(): string
    {
        return 'login_otp:' . Str::lower($this->email);
    }

    protected function ensureRateLimit(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Try again in {$seconds} seconds."
            ]);
        }
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email) . '|' . request()->ip();
    }
}
?>
<div class="flex flex-col gap-6" wire:poll.1s="tick">

    <x-auth-header 
        :title="'Log in to your account'" 
        :description="'Enter your email and password below to log in'" 
    />

    @if(!$showOtpForm)
        <form wire:submit.prevent="login" class="flex flex-col gap-4">
            <flux:input wire:model="email" label="Email address" type="email" required />
            <flux:input wire:model="password" label="Password" type="password" required />
            <flux:checkbox wire:model="remember" label="Remember me" />

            <div class="text-right text-sm">
                <button type="button" wire:click="sendOtp" class="text-blue-600 hover:underline">
                    Forgot Password?
                </button>
            </div>

            @if ($remainingSeconds > 0)
                <div class="text-center text-red-500">
                    Please wait <b>{{ $remainingSeconds }}</b> seconds before next attempt.
                </div>
            @endif

            <flux:button type="submit" variant="primary" class="w-full">Log in</flux:button>
        </form>
    @endif

    @if($showOtpForm)
        <div class="mt-6 p-4 border rounded-lg bg-gray-50">
            <p class="text-sm text-gray-700 mb-2">Enter the OTP sent to your email:</p>
            <form wire:submit.prevent="loginWithOtp" class="flex flex-col gap-4">
                <flux:input wire:model="otpCode" label="OTP" type="text" maxlength="6" required />
                <flux:button type="submit" variant="primary" class="w-full">
                    Login with OTP
                </flux:button>
            </form>

            <div class="text-right mt-2 text-sm">
                <button type="button" wire:click="sendOtp" @if($resendCooldown>0) disabled @endif class="text-blue-600 hover:underline">
                    @if($resendCooldown>0)
                        Resend OTP in {{ $resendCooldown }}s
                    @else
                        Resend OTP
                    @endif
                </button>
            </div>
        </div>
    @endif
</div>

@if(session()->has('success'))
<script>
    Swal.fire({
        title: 'Success!',
        text: "{{ session('success') }}",
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif
