<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Login Mitra</h2>
        <p class="text-sm text-gray-600">Portal PPAT dan Freelance</p>
    </div>

    <form method="POST" action="{{ route('mitra.login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
        
        <div class="mt-4 text-center border-t border-gray-200 pt-4">
            <p class="text-sm text-gray-600">Belum memiliki akun?</p>
            <a href="{{ route('mitra.register') }}" class="inline-block mt-2 font-medium text-indigo-600 hover:text-indigo-500">
                Daftar Sebagai Mitra
            </a>
        </div>
    </form>
</x-guest-layout>