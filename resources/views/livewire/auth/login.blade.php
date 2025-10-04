@extends('layouts.app')

@section('content')
    <livewire:auth.login />
@endsection

<div class="flex flex-col gap-6 max-w-md mx-auto mt-10"
     x-data="{ showCaptcha: @entangle('showCaptcha'), countdown: @entangle('countdown'), captchaReady: @entangle('captchaReady') }"
     x-init="
        $watch('showCaptcha', value => {
            if(value){
                countdown = 20;
                captchaReady = false;
                let timer = setInterval(() => {
                    if(countdown > 0) countdown--;
                    else { captchaReady = true; clearInterval(timer); }
                }, 1000);
            }
        });
     "
>
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
        <!-- Email -->
        <flux:input wire:model="email" type="email" required autofocus placeholder="email@example.com" />

        <!-- Password -->
        <flux:input wire:model="password" type="password" required placeholder="Password" viewable />

        <!-- CAPTCHA -->
        <template x-if="showCaptcha">
            <div class="flex flex-col gap-2">
                <template x-if="!captchaReady">
                    <span class="text-red-500 font-bold">
                        Please wait <span x-text="countdown"></span> seconds before CAPTCHA.
                    </span>
                </template>

                <template x-if="captchaReady">
                    <div class="g-recaptcha"
                         data-sitekey="{{ config('services.recaptcha.key') }}"
                         data-callback="setRecaptchaToken">
                    </div>
                </template>
            </div>
        </template>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="'Remember me'" />

        <!-- Submit -->
        <flux:button type="submit" variant="primary" class="w-full">Log in</flux:button>
    </form>

    @if(Route::has('register'))
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400 mt-4">
            {{ __("Don't have an account?") }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
function setRecaptchaToken(token) {
    @this.set('captchaToken', token);
}
</script>

