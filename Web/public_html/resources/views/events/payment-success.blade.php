<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-[#0a0a0a] text-white flex items-center justify-center min-h-screen p-6 relative overflow-hidden">
    <!-- Background Gradient Glows -->
    <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full bg-[#6b6b4b]/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-96 h-96 rounded-full bg-emerald-500/5 blur-[120px] pointer-events-none"></div>

    <div class="bg-[#121212]/90 backdrop-blur-md p-8 md:p-10 rounded-3xl border border-[#222222] max-w-md w-full text-center shadow-2xl relative z-10">
        <!-- Success Icon -->
        <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl md:text-3xl font-black tracking-tight mb-4 uppercase text-white">Payment Successful!</h1>
        <p class="text-[#8b8b6b] text-base mb-8 leading-relaxed">
            Your event registration has been confirmed successfully. You can return to the dashboard or close this window to go back to the app.
        </p>

        <!-- Actions -->
        <div class="space-y-4">
            <a href="/user/events" class="w-full block bg-[#6b6b4b] hover:bg-[#7b7b5b] active:scale-[0.98] text-white font-bold py-4 px-6 rounded-2xl transition-all duration-200 uppercase tracking-widest text-xs shadow-lg shadow-[#6b6b4b]/20 text-center">
                Go to Events Dashboard
            </a>
        </div>
    </div>
</body>
</html>
