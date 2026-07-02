<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
        <p class="text-[#8b8b6b] text-sm">Please enter your details to sign in</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" required autofocus 
                class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[#2a2a2a] bg-[#0a0a0a] text-[#6b6b4b] shadow-sm focus:ring-[#6b6b4b]" name="remember">
                <span class="ms-2 text-sm text-[#8b8b6b]">Remember me</span>
            </label>
        </div>

        <div class="flex flex-col gap-4 mt-4">
            <button class="w-full bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white py-3 rounded-lg font-bold shadow-lg shadow-[#6b6b4b]/20 transition-all">
                Log In
            </button>

            @if (Route::has('register'))
                <a class="text-sm text-[#8b8b6b] hover:text-white text-center transition-colors" href="{{ route('register') }}">
                    Don't have an account? <span class="text-[#6b6b4b] font-bold">Register</span>
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>