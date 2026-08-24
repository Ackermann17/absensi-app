<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Absensi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <!-- Tambahkan x-data untuk state sidebar (Alpine.js) -->
    <body class="font-sans antialiased text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">
        
        <!-- Wadah Flexbox Utama (Sidebar + Konten) -->
        <div class="flex h-screen overflow-hidden relative">
            
            <!-- Overlay Gelap untuk Mobile (Aktif jika sidebar terbuka) -->
            <div x-show="sidebarOpen" 
                 x-transition.opacity 
                 class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden" 
                 @click="sidebarOpen = false" style="display: none;"></div>

            <!-- SECTION 1: SIDEBAR -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                   class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-slate-200 flex flex-col shadow-xl lg:shadow-none transition-transform duration-300 lg:static lg:translate-x-0">
                
                <!-- Logo & Branding -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center mr-3 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="font-bold text-lg text-slate-900 tracking-tight">Admin Panel</span>
                    </div>
                    <!-- Tombol Tutup Sidebar (Hanya tampil di Mobile) -->
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Navigasi Menu -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>

                    <!-- Data Murid -->
                    <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('employees.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Data Murid
                    </a>

                    <!-- Izin & Sakit -->
                    <a href="{{ route('leaves.approval') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('leaves.*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Izin & Sakit
                    </a>
                    
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <a href="{{ route('attendance.terminal') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            Buka Terminal
                        </a>
                    </div>
                </nav>

                <!-- Info User, Profile & Logout -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <!-- Info Nama User -->
                    <div class="flex items-center gap-3 mb-3 px-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold uppercase">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Tombol Profil Akun (Baru) -->
                    <a href="{{ route('profile') }}" class="w-full text-left px-3 py-2 text-sm text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Profil Akun
                    </a>

                    <!-- Form Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 font-medium hover:bg-red-50 rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <!-- SECTION 2: AREA KONTEN UTAMA -->
            <div class="flex-1 flex flex-col overflow-hidden bg-slate-50 w-full">
                
                <!-- Header (Burger Menu untuk Mobile & Teks Judul) -->
                <header class="bg-white border-b border-slate-200 shadow-sm z-10 flex items-center h-16">
                    <!-- Tombol Burger Menu (Muncul hanya di Mobile) -->
                    <button @click="sidebarOpen = true" class="p-4 text-slate-500 hover:text-slate-700 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    @if (isset($header))
                        <div class="px-4 sm:px-6 flex-1">
                            {{ $header }}
                        </div>
                    @endif
                </header>

                <!-- Konten Inti Halaman -->
                <main class="flex-1 overflow-y-auto w-full">
                    {{ $slot }}
                </main>
            </div>
            
        </div>
    </body>
</html>