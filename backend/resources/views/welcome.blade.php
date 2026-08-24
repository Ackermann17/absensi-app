<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Website Absensi Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col items-center justify-center p-6">

    <div class="w-full max-w-lg text-center">
        <!-- Logo / Header dengan Animasi Bergerak -->
<div class="mb-10 text-center">
    <!-- Wadah Ikon dengan Efek Glow dan Animasi Napas (Pulse) -->
    <div class="relative w-24 h-24 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl ring-4 ring-blue-50 overflow-hidden group">
        
        <!-- Garis Animasi Scan Bergerak ke Atas-Bawah -->
        <div class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-cyan-300 to-transparent shadow-[0_0_12px_#38bdf8] animate-[scan_2s_ease-in-out_infinite]"></div>
        
        <!-- Ikon Kamera / Scanner -->
        <svg class="w-11 h-11 relative z-10 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"></path>
        </svg>
    </div>
    
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sistem Absensi Digital</h1>
    <p class="text-slate-500 mt-2 font-medium">Arahkan QR Code Anda ke kamera untuk mulai</p>
</div>
        <!-- Tombol Utama Akses Terminal -->
        <a href="{{ route('attendance.terminal') }}" class="group block w-full bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 hover:ring-blue-500 hover:shadow-xl transition-all duration-300 p-6 cursor-pointer transform hover:-translate-y-1">
            <div class="flex items-center justify-center gap-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-xl font-bold text-slate-800">Buka Kamera Scanner</span>
            </div>
        </a>

        <!-- Akses Admin (Subtle) -->
        <!-- Akses Admin (Tombol Kotak Elegan) -->
<div class="mt-12 flex justify-center">
    <a href="{{ route('login') }}" class="group inline-flex items-center gap-2.5 px-5 py-2.5 bg-white hover:bg-slate-900 text-slate-700 hover:text-white rounded-xl shadow-sm ring-1 ring-slate-200 hover:ring-slate-900 text-sm font-semibold transition-all duration-300 transform hover:-translate-y-0.5">
        <!-- Ikon Gembok -->
        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        Login Admin
    </a>
</div>
    </div>

</body>
</html>