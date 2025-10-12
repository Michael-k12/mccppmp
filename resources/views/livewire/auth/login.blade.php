<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
    public int $maxOtpAttempts = 3;
    public int $otpExpireMinutes = 2;
    public int $resendCooldown = 0;
    public int $resendSeconds = 30;

    protected ?\App\Models\User $pendingUser = null;

    public function tick(): void
    {
        if ($this->remainingSeconds > 0) $this->remainingSeconds--;
        if ($this->resendCooldown > 0) $this->resendCooldown--;
    }

    /**
     * Step 1: Validate credentials + reCAPTCHA + send OTP
     */
    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        // 🔐 Verify reCAPTCHA
        $token = request('g-recaptcha-response');
        if (!$token) {
            throw ValidationException::withMessages([
                'email' => 'reCAPTCHA validation missing. Please try again.',
            ]);
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        $recaptcha = $response->json();
        if (!($recaptcha['success'] ?? false) || ($recaptcha['score'] ?? 0) < 0.5) {
            throw ValidationException::withMessages([
                'email' => 'reCAPTCHA verification failed. Please try again.',
            ]);
        }

        // ✅ Validate credentials
        if (!Auth::validate(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($this->throttleKey(), 30);
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($this->throttleKey());
        Cache::forget($this->otpCacheKey());

        $user = \App\Models\User::where('email', $this->email)->first();
        $this->pendingUser = $user;

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
    }

    /**
     * Step 2: Verify OTP and log in
     */
    public function loginWithOtp(): void
    {
        $this->validate(['otpCode' => 'required|digits:6']);

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
        throw ValidationException::withMessages(['email' => "Too many login attempts. Try again in {$seconds} seconds."]);
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email) . '|' . request()->ip();
    }
}
?>
<style>
/* ✅ Force the Google reCAPTCHA v3 badge to remain visible and clickable */
.grecaptcha-badge {
    visibility: visible !important;
    opacity: 1 !important;
    display: block !important;
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 99999 !important;
    transform: scale(1) !important;
    transition: opacity 0.3s ease-in-out !important;
}

/* Optional: ensure nothing covers the badge */
body {
    padding-bottom: 60px; /* add spacing so it doesn’t overlap footer */
}
</style>

<div class="flex flex-col gap-6" wire:poll.1s="tick">
    <x-auth-header 
        :title="'Log in to your account'" 
        :description="'Enter your email and password below to log in'" 
    />

    @if(!$showOtpForm)
        <form wire:submit.prevent="login" class="flex flex-col gap-4" id="login-form">
            <flux:input wire:model="email" label="Email address" type="email" required />
            <flux:input wire:model="password" label="Password" type="password" required />

            @if ($remainingSeconds > 0)
                <div class="text-center text-red-500">
                    Please wait <b>{{ $remainingSeconds }}</b> seconds before next attempt.
                </div>
            @endif

            <!-- Hidden reCAPTCHA token -->
            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

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

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
document.addEventListener('submit', function(e) {
    if (!e.target.matches('#login-form')) return;
    e.preventDefault();

    grecaptcha.ready(function() {
        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
            Livewire.find(e.target.closest('[wire\\:id]').getAttribute('wire:id')).call('login');
        });
    });
});
</script>
@endpush
