<div class="flex flex-col gap-6">
    {{-- Header --}}
    <div class="text-center space-y-2">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
            Selamat Datang Kembali
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
            Masuk ke akun Anda untuk melanjutkan
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-5">
        <!-- Email Address -->
        <div class="space-y-2">
            <flux:input wire:model="email" :label="__('Email')" type="email" required autofocus autocomplete="email"
                placeholder="nama@email.com" icon="envelope" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:label>{{ __('Password') }}</flux:label>
                @if (Route::has('password.request'))
                    <flux:link
                        class="text-sm text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                        :href="route('password.request')" wire:navigate>
                        {{ __('Lupa password?') }}
                    </flux:link>
                @endif
            </div>
            <flux:input wire:model="password" type="password" required autocomplete="current-password"
                placeholder="••••••••" viewable icon="lock-closed" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <flux:checkbox wire:model="remember" :label="__('Ingat saya')" />
        </div>

        <flux:button variant="primary" type="submit"
            class="w-full text-white bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-700 hover:from-emerald-700 hover:via-emerald-800 hover:to-teal-800 shadow-lg py-3 text-base font-semibold">
            <span wire:loading.remove wire:target="login">{{ __('Masuk') }}</span>
        </flux:button>
    </form>

    @if (Route::has('register'))
        <div class="text-center">
            <p class="text-sm text-gray-700 dark:text-gray-400">
                {{ __('Belum punya akun?') }}
                <flux:link
                    class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                    :href="route('register')" wire:navigate>
                    {{ __('Daftar sekarang') }}
                </flux:link>
            </p>
        </div>
    @endif
</div>