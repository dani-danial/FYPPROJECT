@extends('layouts.app')

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">SOS Signals Management</h2>
            <p class="text-[#8b8b6b] mt-1 text-sm">Monitor and respond to emergency signals from users.</p>
        </div>
        <a href="{{ route('sos.create') }}" 
            class="bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-500/30 px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 transition-all text-sm font-medium">
            <i data-lucide="siren" class="w-4 h-4"></i>
            <span>Simulate Incoming SOS</span>
        </a>
    </div>

    @if($pendingSignals > 0)
    <div class="mb-8 bg-red-500/10 border border-red-500/20 rounded-xl p-4 flex items-center justify-between animate-pulse">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-red-500/20 rounded-full text-red-400">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-red-400 font-bold">Action Required</h3>
                <p class="text-red-300/70 text-sm">{{ $pendingSignals }} pending signals need attention.</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Total</h3>
            <div class="text-4xl font-bold text-white">{{ $totalSignals }}</div>
        </div>
        <div class="bg-red-900/20 border border-red-500/30 rounded-xl p-6">
            <h3 class="text-red-400/70 text-xs font-bold uppercase tracking-wider mb-2">Pending</h3>
            <div class="text-4xl font-bold text-red-400">{{ $pendingSignals }}</div>
        </div>
        <div class="bg-yellow-900/20 border border-yellow-500/30 rounded-xl p-6">
            <h3 class="text-yellow-400/70 text-xs font-bold uppercase tracking-wider mb-2">Ongoing</h3>
            <div class="text-4xl font-bold text-yellow-400">{{ $ongoingSignals }}</div>
        </div>
        <div class="bg-green-900/20 border border-green-500/30 rounded-xl p-6">
            <h3 class="text-green-400/70 text-xs font-bold uppercase tracking-wider mb-2">Resolved</h3>
            <div class="text-4xl font-bold text-green-400">{{ $resolvedSignals }}</div>
        </div>
    </div>

    <div class="space-y-6">
        @foreach($signals as $signal)
        @php
            $cardBorder = match($signal->status) {
                'pending' => 'border-red-500/30',
                'ongoing' => 'border-yellow-500/30',
                'resolved' => 'border-green-500/30',
            };
        @endphp
        <div class="bg-[#1a1a1a] border {{ $cardBorder }} rounded-xl p-6 transition-all">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-12 h-12 rounded-lg bg-black/20 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-white mb-1">{{ $signal->user_name }}</h3>
                    <p class="text-white/80">{{ $signal->message }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-[#0a0a0a] rounded-lg border border-[#2a2a2a]">
                <div class="text-sm text-[#b0b0a0]">Contact: <span class="text-white">{{ $signal->phone_number }}</span></div>
                <div class="text-sm text-[#b0b0a0]">Location: <span class="text-white">{{ $signal->location_name }}</span></div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="https://www.google.com/maps/search/?api=1&query={{ $signal->latitude }},{{ $signal->longitude }}" target="_blank"
                   class="px-4 py-2 bg-[#2a2a2a] text-white rounded-lg text-sm transition-colors">View Map</a>

                @if($signal->status !== 'resolved')
                    @if($signal->status === 'pending')
                    <form action="{{ route('sos.updateStatus', $signal->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="ongoing">
                        <button type="submit" class="px-4 py-2 bg-yellow-900/20 text-yellow-400 rounded-lg text-sm">Ongoing</button>
                    </form>
                    @endif
                    <form action="{{ route('sos.updateStatus', $signal->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="px-4 py-2 bg-green-900/20 text-green-400 rounded-lg text-sm">Resolved</button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endsection