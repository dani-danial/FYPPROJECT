@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('sos.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Dashboard
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-2">Simulate SOS Signal</h2>
        <p class="text-[#8b8b6b] mb-6 text-sm">Manually create an alert to test the system response.</p>

        <form action="{{ route('sos.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Runner Name</label>
                    <input type="text" name="user_name" placeholder="e.g. John Doe" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Phone Number</label>
                    <input type="text" name="phone_number" placeholder="e.g. +60123456789" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Distress Message</label>
                <textarea name="message" rows="3" required placeholder="e.g. Injured leg, cannot walk. Need assistance."
                          class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]"></textarea>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Location Name</label>
                <input type="text" name="location_name" placeholder="e.g. Central Park, Section B" required
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-sm shadow-lg shadow-red-900/20">
                    Trigger Alert
                </button>
            </div>
        </form>
    </div>
</div>
@endsection