@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-4 mb-10">
            <a href="{{ route('user.events') }}" class="p-2 bg-[#1a1a1a] rounded-lg text-[#4a4a4a] hover:text-white transition-all">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight uppercase">Confirm Payment</h2>
                <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Review event details before proceeding</p>
            </div>
        </div>

        {{-- Payment Card --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            
            {{-- Event Details --}}
            <div class="bg-black/40 p-8 rounded-[2rem] border border-[#2a2a2a] mb-8">
                <h3 class="text-[10px] font-black text-[#6b6b4b] uppercase tracking-[0.3em] mb-6">Event Details</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Event Name:</span>
                        <span class="text-white font-bold text-lg">{{ $event->title }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Location:</span>
                        <span class="text-white font-bold">{{ $event->location }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Date:</span>
                        <span class="text-white font-bold">{{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}</span>
                    </div>
                    <div class="h-px bg-[#2a2a2a] my-4"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Distance:</span>
                        <span class="text-white font-bold">{{ $event->distance_km }} km</span>
                    </div>
                </div>
            </div>

            {{-- Payer Details --}}
            <div class="bg-black/40 p-8 rounded-[2rem] border border-[#2a2a2a] mb-8">
                <h3 class="text-[10px] font-black text-[#6b6b4b] uppercase tracking-[0.3em] mb-6">Your Details</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Name:</span>
                        <span class="text-white font-bold">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Email:</span>
                        <span class="text-white font-bold text-sm">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8b8b6b] text-sm">Phone:</span>
                        <span class="text-white font-bold">{{ $user->phone }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Amount --}}
            <div class="bg-[#6b6b4b]/20 border border-[#6b6b4b] p-8 rounded-[2rem] mb-8">
                <div class="flex justify-between items-center">
                    <span class="text-[#8b8b6b] text-lg font-bold uppercase">Total Payment:</span>
                    <span class="text-[#6b6b4b] text-4xl font-black">RM {{ number_format($event->entry_fee, 2) }}</span>
                </div>
            </div>

            {{-- Terms --}}
            <div class="bg-black/40 p-6 rounded-xl border border-[#2a2a2a] mb-8">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" id="agree_terms" class="w-4 h-4 rounded accent-[#6b6b4b]" required>
                    <span class="text-[#8b8b6b] text-sm">I agree to pay <strong>RM {{ number_format($event->entry_fee, 2) }}</strong> for this event registration</span>
                </label>
            </div>

            {{-- Action Buttons --}}
            <form action="{{ route('user.events.confirmPayment', $event->id) }}" method="POST" class="flex gap-4">
                @csrf
                
                <a href="{{ route('user.events') }}" class="flex-1 py-4 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all text-center">
                    Cancel
                </a>

                <button type="submit" id="pay_btn" disabled class="flex-1 py-4 bg-[#6b6b4b] hover:bg-[#7b7b5b] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-[#6b6b4b]/20">
                    Proceed to Payment
                </button>
            </form>

            <p class="text-center text-[#4a4a4a] text-xs uppercase tracking-widest mt-6">
                You will be redirected to Toyyibpay to complete your payment securely
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('agree_terms').addEventListener('change', function() {
    document.getElementById('pay_btn').disabled = !this.checked;
});
</script>
@endsection
