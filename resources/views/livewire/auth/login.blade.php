<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Mail\LoginOtpMail;
use App\Mail\SecurityAlertMail; // ✅ Added

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
    public int $maxOtpAttempts = 3;
    public int $otpExpireMinutes = 2;
    public int $resendCooldown = 0;
    public int $resendSeconds = 30;

    public string $recaptcha_token = '';

    protected ?\App\Models\User $pendingUser = null;

    public function tick(): void
    {
        if ($this->remainingSeconds > 0) $this->remainingSeconds--;
        if ($this->resendCooldown > 0) $this->resendCooldown--;
    }

    /**
     * Step 1: Validate credentials → send OTP
     */
    public function login(): void
    {
        try {
            $this->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
                'recaptcha_token' => 'required|string',
            ]);

            // ✅ Verify Google reCAPTCHA
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('recaptcha.secret_key'),
                'response' => $this->recaptcha_token,
                'remoteip' => request()->ip(),
            ]);

            $google = $response->json();

            if (!$google['success'] || ($google['score'] ?? 0) < 0.5) {
                throw ValidationException::withMessages([
                    'email' => 'reCAPTCHA verification failed. Please try again.',
                ]);
            }

            $this->ensureIsNotRateLimited();

            // Check credentials manually
            if (!Auth::validate(['email' => $this->email, 'password' => $this->password])) {
                RateLimiter::hit($this->throttleKey(), 30);

                // ✅ Send suspicious activity alert
                Mail::to(config('mail.from.address'))->send(new SecurityAlertMail(
                    'Suspicious Login Attempt Detected',
                    'Failed login attempt for email: ' . $this->email . ' from IP: ' . request()->ip() . ' at ' . now()
                ));

                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // ✅ Credentials correct → reset limiter
            RateLimiter::clear($this->throttleKey());
            Cache::forget($this->otpCacheKey());

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

            Mail::to($this->email)->send(new LoginOtpMail($otp));

            $this->showOtpForm = true;
            $this->resendCooldown = $this->resendSeconds;

            session()->flash('success', "OTP sent to your email (valid for {$this->otpExpireMinutes} minutes).");
        } catch (\Throwable $e) {
            // ✅ Log and notify via email on error
            Mail::to(config('mail.from.address'))->send(new SecurityAlertMail(
                'System Error During Login',
                'Error message: ' . $e->getMessage() . ' | User: ' . $this->email . ' | IP: ' . request()->ip()
            ));

           $this->js("
    Swal.fire({
        title: 'Login Error',
        text: 'Something went wrong. Please try again later.',
        icon: 'error'
    });
");


            throw $e;
        }
    }

    /**
     * Step 2: Validate OTP and log in
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

        $user = \App\Models\User::where('email', $otpData['email'])->first();
        Auth::login($user, $otpData['remember']);

        // ✅ Successful login → send activity log email
        Mail::to(config('mail.from.address'))->send(new SecurityAlertMail(
            'User Login Successful',
            'User ' . $user->name . ' (' . $user->email . ') successfully logged in at ' . now() . ' from IP: ' . request()->ip()
        ));

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
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 3)) return;

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $this->remainingSeconds = $seconds;

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
<div class="flex flex-col gap-6" wire:poll.1s="tick">
    <x-auth-header 
        :title="'Log in to your account'" 
        :description="'Enter your email and password below to log in'" 
    />

    <!-- Normal login -->
    @if(!$showOtpForm)
        <form wire:submit.prevent="login" class="flex flex-col gap-4">
            <flux:input wire:model="email" label="Email address" type="email" required />
            <flux:input wire:model="password" label="Password" type="password" required />

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

            <!-- ✅ reCAPTCHA attribution text -->
            <p class="text-xs text-gray-400 text-center mt-2">
                This site is protected by reCAPTCHA and the Google
                <a href="https://policies.google.com/privacy" class="text-blue-500" target="_blank">Privacy Policy</a> and
                <a href="https://policies.google.com/terms" class="text-blue-500" target="_blank">Terms of Service</a> apply.
            </p>
        </form>
    @endif

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __("Don't have an account?") }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif

    <!-- OTP Login -->
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
                <button type="button"
                        wire:click="resendOtp"
                        @if($resendCooldown > 0) disabled @endif
                        class="text-blue-600 hover:underline">
                    @if($resendCooldown > 0)
                        Resend OTP in {{ $resendCooldown }}s
                    @else
                        Resend OTP
                    @endif
                </button>
            </div>
        </div>
    @endif

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
</div>

<!-- ✅ Google reCAPTCHA v3 Visible Badge -->
<script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>

<script>
document.addEventListener('livewire:init', () => {
    function setRecaptchaToken() {
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
                Livewire.find(
                    document.querySelector('[wire\\:id]').getAttribute('wire:id')
                ).set('recaptcha_token', token);
            });
        });
    }

    setRecaptchaToken();
    setInterval(setRecaptchaToken, 90000); // refresh every 90s
});
</script>
