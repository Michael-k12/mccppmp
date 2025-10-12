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

    // OTP Properties
    public bool $showOtpForm = false;
    public string $otpCode = '';
    public int $maxOtpAttempts = 5;
    public int $otpExpireMinutes = 5;
    public int $resendCooldown = 0;
    public int $resendSeconds = 30;

    // To store temporarily verified credentials
    protected ?\App\Models\User $pendingUser = null; // 🔹

    public function tick(): void
    {
        if ($this->remainingSeconds > 0) $this->remainingSeconds--;
        if ($this->resendCooldown > 0) $this->resendCooldown--;
    }

    /**
     * Step 1: Validate credentials → send OTP (do not log in yet)
     */
    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        // Check credentials manually
        if (!Auth::validate(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($this->throttleKey(), 60);
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 🔹 Credentials correct → proceed to OTP verification
        $user = \App\Models\User::where('email', $this->email)->first();
        $this->pendingUser = $user;

        // Generate OTP
        $otp = random_int(100000, 999999);
        $cacheKey = $this->otpCacheKey();

        Cache::put($cacheKey, [
            'otp' => bcrypt($otp),
            'attempts' => 0,
            'user_id' => $user->id,
            'email' => $user->email,
            'remember' => $this->remember,
        ], now()->addMinutes($this->otpExpireMinutes));

        // Send mail
        Mail::to($this->email)->send(new LoginOtpMail($otp));

        // Show OTP form
        $this->showOtpForm = true;
        $this->resendCooldown = $this->resendSeconds;

        session()->flash('success', "OTP sent to your email (valid for {$this->otpExpireMinutes} minutes).");
    }

    /**
     * Step 2: Validate OTP and log the user in
     */
    public function loginWithOtp(): void
    {
        $this->validate([
            'otpCode' => 'required|digits:6',
        ]);

        $cacheKey = $this->otpCacheKey();
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            $this->addError('otpCode', 'OTP expired. Please log in again.');
            return;
        }

        if ($otpData['attempts'] >= $this->maxOtpAttempts) {
            Cache::forget($cacheKey);
            $this->addError('otpCode', 'Maximum OTP attempts exceeded. Please log in again.');
            return;
        }

        if (!password_verify($this->otpCode, $otpData['otp'])) {
            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addMinutes($this->otpExpireMinutes));
            $this->addError('otpCode', 'Invalid OTP.');
            return;
        }

        // 🔹 OTP valid → authenticate user
        $user = \App\Models\User::where('email', $otpData['email'])->first();
        Auth::login($user, $otpData['remember']);

        Cache::forget($cacheKey);
        $this->showOtpForm = false;

        $this->redirect(route('dashboard'));
    }

    public function resendOtp(): void
    {
        if ($this->resendCooldown > 0) {
            $this->addError('otpCode', "Please wait {$this->resendCooldown} seconds before resending OTP.");
            return;
        }

        // Use same logic as first send
        $otp = random_int(100000, 999999);
        $cacheKey = $this->otpCacheKey();

        Cache::put($cacheKey, [
            'otp' => bcrypt($otp),
            'attempts' => 0,
            'user_id' => \App\Models\User::where('email', $this->email)->value('id'),
            'email' => $this->email,
            'remember' => $this->remember,
        ], now()->addMinutes($this->otpExpireMinutes));

        Mail::to($this->email)->send(new LoginOtpMail($otp));
        $this->resendCooldown = $this->resendSeconds;

        session()->flash('success', "New OTP sent to your email.");
    }

    protected function otpCacheKey(): string
    {
        return 'login_otp:' . Str::lower($this->email);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) return;

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Try again in {$seconds} seconds.",
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email) . '|' . request()->ip();
    }
}
?>
