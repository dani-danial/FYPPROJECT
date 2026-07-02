@extends('layouts.app')

@section('content')
{{-- 1. Load Cropper.js CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-3xl font-black text-white tracking-tight mb-2">Account Settings</h2>
            <p class="text-[#4a4a4a] text-sm mb-10 font-bold uppercase tracking-widest">Update your runner profile</p>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PATCH')

                {{-- Profile Picture Section --}}
                <div class="flex items-center gap-6">
                    {{-- Preview Container --}}
                    <div class="w-24 h-24 bg-[#6b6b4b] rounded-full flex items-center justify-center text-white text-3xl font-bold overflow-hidden border-4 border-[#2a2a2a]">
                        {{-- 🛠️ FIXED: Using the Accessor profile_photo_url with cache-busting timestamp --}}
                        <img id="preview-image" src="{{ Auth::user()->profile_photo_url }}?t={{ time() }}" 
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.parentElement.innerText='{{ substr(Auth::user()->name, 0, 1) }}'">
                    </div>
                    
                    <div class="flex-1 space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest mb-2">Profile Photo</label>
                        
                        {{-- File Input --}}
                        <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*"
                               class="text-xs text-[#4a4a4a] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-[#2a2a2a] file:text-[#8b8b6b] hover:file:text-white cursor-pointer">
                        
                        @if(Auth::user()->profile_photo_path)
                            <button type="submit" name="remove_photo" value="1" class="block text-[10px] font-bold text-red-500 uppercase tracking-widest hover:text-red-400 transition-colors">
                                Remove Current Photo
                            </button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Display Name</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" 
                            style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Username</label>
                        <input type="text" name="username" value="{{ old('username', Auth::user()->username) }}" 
                            style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="e.g., +60123456789"
                            style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all">
                    </div>
                </div>

                {{-- FITNESS STATS SECTION --}}
                <div class="bg-black/40 p-8 rounded-[2rem] border border-[#2a2a2a] space-y-6">
                    <h3 class="text-[10px] font-black text-[#6b6b4b] uppercase tracking-[0.3em]">AI Coach Data (Fitness Bio)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight_kg" value="{{ old('weight_kg', Auth::user()->weight_kg) }}" 
                                style="background-color: black !important; color: white !important;"
                                class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all">
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Height (cm)</label>
                            <input type="number" step="0.1" name="height_cm" value="{{ old('height_cm', Auth::user()->height_cm) }}" 
                                style="background-color: black !important; color: white !important;"
                                class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all">
                        </div>
                        {{-- 🛠️ ADDED: Base Pace Field to match Controller logic --}}
                        <div class="space-y-3">
                            <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Base Pace (min/km)</label>
                            <input type="text" name="base_pace_min_km" value="{{ old('base_pace_min_km', Auth::user()->base_pace_min_km) }}" 
                                placeholder="e.g., 5:30"
                                style="background-color: black !important; color: white !important;"
                                class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all">
                        </div>
                    </div>
                </div>

                {{-- About Section --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">About You</label>
                    {{-- 🛠️ FIXED: Changed name to about_me to match DB and Controller --}}
                    <textarea name="about_me" rows="4" 
                        style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-4 outline-none focus:border-[#6b6b4b] transition-all resize-none">{{ old('about_me', Auth::user()->about_me) }}</textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        class="w-full py-4 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-[#6b6b4b]/20">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. CROP MODAL --}}
<div id="crop-modal" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-[#1a1a1a] rounded-2xl p-6 w-full max-w-lg border border-[#2a2a2a] shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white text-lg font-bold">Crop Profile Picture</h3>
            <button type="button" onclick="closeCropModal()" class="text-[#4a4a4a] hover:text-white transition-colors">
                Cancel
            </button>
        </div>
        
        <div class="h-80 w-full bg-black mb-6 overflow-hidden rounded-xl border border-[#2a2a2a]">
            <img id="image-to-crop" class="max-w-full block">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeCropModal()" class="px-6 py-3 rounded-xl border border-[#2a2a2a] text-[#8b8b6b] hover:text-white font-bold text-xs uppercase transition-all">
                Cancel
            </button>
            <button type="button" id="crop-button" class="px-6 py-3 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-xl font-bold text-xs uppercase transition-all shadow-lg">
                Crop & Apply
            </button>
        </div>
    </div>
</div>

{{-- 3. CROPPER SCRIPTS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let cropper;
    const modal = document.getElementById('crop-modal');
    const image = document.getElementById('image-to-crop');
    const input = document.getElementById('profile_picture_input');
    const previewImage = document.getElementById('preview-image');

    input.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                image.src = e.target.result;
                modal.classList.remove('hidden');
                
                if(cropper) cropper.destroy();
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    background: false,
                    autoCropArea: 0.8,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('crop-button').addEventListener('click', function() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
        });

        previewImage.src = canvas.toDataURL();
        previewImage.style.display = 'block';

        canvas.toBlob(function(blob) {
            const newFile = new File([blob], 'profile_cropped.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);
            input.files = dataTransfer.files;

            closeCropModal();
        });
    });

    function closeCropModal() {
        modal.classList.add('hidden');
        if(cropper) {
            cropper.destroy();
            cropper = null;
        }
    }
</script>
@endsection