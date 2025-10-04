<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public bool $showCaptcha = false;
    public bool $captchaReady = false;
    public int $countdown = 20;
    public string $captchaToken = '';

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $attempts = RateLimiter::attempts($this->throttleKey());

        // Full lockout after 5 failed attempts
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please wait $seconds seconds.",
            ]);
        }

        // Enable CAPTCHA after 3 failed attempts
        if ($attempts >= 3) {
            $this->showCaptcha = true;

            if (!$this->captchaReady) {
                throw ValidationException::withMessages([
                    'email' => "Please wait for CAPTCHA to be ready after {$this->countdown} seconds.",
                ]);
            }

            if (!$this->validateRecaptcha()) {
                throw ValidationException::withMessages([
                    'email' => 'CAPTCHA verification failed.',
                ]);
            }
        }

        // Attempt login
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 600); // 10 minutes
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Success
        RateLimiter::clear($this->throttleKey());
        $this->showCaptcha = false;
        $this->captchaReady = false;
        $this->captchaToken = '';
        Session::regenerate();

        return redirect()->intended(route('dashboard'));
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email) . '|' . request()->ip();
    }

    protected function validateRecaptcha(): bool
    {
        $token = $this->captchaToken ?: request()->input('g-recaptcha-response');
        if (! $token) return false;

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ])->json();

        return $response['success'] ?? false;
    }

    public function mount()
    {
        $this->showCaptcha = false;
        $this->captchaReady = false;
        $this->countdown = 20;
    }
}
