<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-white">Create Account</h2>
        <p class="text-[#8b8b6b] text-sm">Join the community today</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Name</label>
            <input id="name" class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all" 
                   type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Username</label>
            <input id="username" class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all" 
                   type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Email Address</label>
            <input id="email" class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all" 
                   type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Password</label>
            <input id="password" class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all"
                   type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label class="text-sm font-medium text-[#b0b0a0]">Confirm Password</label>
            <input id="password_confirmation" class="block mt-1 w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all"
                   type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-[#8b8b6b] hover:text-white transition-colors" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white px-6 py-3 rounded-lg font-bold shadow-lg shadow-[#6b6b4b]/20 transition-all uppercase text-xs tracking-widest">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>