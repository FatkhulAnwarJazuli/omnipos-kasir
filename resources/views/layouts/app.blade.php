<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'POS Markasnack & Glytch') }}</title>

        <!-- Fonts & Scripts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            /* Mencegah elemen Alpine.js muncul sekilas saat page load */
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="antialiased bg-gray-50 font-sans" x-data="{ theme: '{{ request('brand', 'default') }}', mobileMenuOpen: false }">
        <div class="min-h-screen flex flex-col md:flex-row">

            <!-- MOBILE BACKDROP OVERLAY -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="fixed inset-0 bg-gray-900/60 z-40 md:hidden backdrop-blur-sm"></div>

            <!-- SIDEBAR RESPONSIVE (HP Drawer / PC Fixed) -->
            <aside class="fixed md:static inset-y-0 left-0 z-50 w-72 bg-gray-900 text-gray-300 flex flex-col justify-between p-5 transform transition-transform duration-300 ease-in-out md:translate-x-0 shadow-2xl md:shadow-none"
                   :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
                <div>
                    <!-- Brand Header -->
                    <div class="mb-8 px-2 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-red-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-amber-500/20">
                                P
                            </div>
                            <div>
                                <h1 class="text-lg font-black tracking-wider text-white leading-tight">OMNIPOS</h1>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">Store Management</p>
                            </div>
                        </div>
                        <button @click="mobileMenuOpen = false" class="md:hidden text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20 shadow-sm' : 'hover:bg-gray-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Section: Etalase Kasir -->
                        <div class="pt-6 pb-2">
                            <p class="px-4 text-[11px] font-bold uppercase tracking-wider text-gray-500">Etalase Kasir</p>
                            <div class="mt-2 space-y-2">
                               <!-- Link Markasnack -->
                                <a href="{{ route('pos.index', ['brand' => 'markasnack']) }}"
                                class="group flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold bg-gradient-to-r from-red-950/80 to-red-900/40 text-red-200 border border-red-800/50 hover:border-red-600 shadow-md shadow-red-950/40 transition-all duration-200">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg">🍿</span>
                                        <span>Markasnack</span>
                                    </div>
                                    <span class="text-[10px] bg-red-800/60 text-red-200 px-2 py-0.5 rounded-full uppercase tracking-wider">Gram</span>
                                </a>

                                <!-- Link Glytch -->
                                <a href="{{ route('pos.index', ['brand' => 'glytch']) }}"
                                class="group flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold bg-neutral-900 text-white border border-neutral-700/60 hover:border-neutral-500 shadow-md transition-all duration-200">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg">💍</span>
                                        <span>Glytch</span>
                                    </div>
                                    <span class="text-[10px] bg-neutral-800 text-neutral-300 px-2 py-0.5 rounded-full uppercase tracking-wider">Retail</span>
                                </a>
                            </div>
                        </div>

                        <!-- Section: Manajemen Toko -->
                        <div class="pt-4 pb-2">
                            <p class="px-4 text-[11px] font-bold uppercase tracking-wider text-gray-500">Manajemen Toko</p>
                            <div class="mt-2 space-y-1">
                                <a href="{{ route('products.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-amber-500/10 text-amber-400 font-semibold' : 'hover:bg-gray-800/60 hover:text-white' }}">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span>Stok & Produk</span>
                                </a>

                                <a href="#" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800/60 hover:text-white transition-all duration-200">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Data Supplier</span>
                                </a>

                                <a href="#" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800/60 hover:text-white transition-all duration-200">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    <span>Stock Opname</span>
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- User Profile & Logout Card -->
                <div class="pt-4 border-t border-gray-800/80">
                    <div class="flex items-center justify-between bg-gray-800/50 p-3 rounded-xl border border-gray-700/40">
                        <div class="flex items-center space-x-3 truncate">
                            <div class="w-9 h-9 rounded-lg bg-gray-700 flex items-center justify-center font-bold text-white text-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="truncate">
                                <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-gray-400">Kasir Utama</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition" title="Logout">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- MAIN CONTENT CONTAINER -->
            <div class="flex-1 flex flex-col min-w-0 min-h-screen">

                <!-- TOP HEADER BAR -->
                <header class="sticky top-0 z-30 px-4 py-3 md:px-8 md:py-4 shadow-sm backdrop-blur-md transition-all duration-300 flex items-center justify-between
                        {{ request('brand') === 'markasnack' ? 'bg-red-950 text-white border-b border-red-800' : (request('brand') === 'glytch' ? 'bg-black text-white border-b border-neutral-800' : 'bg-white text-gray-800 border-b border-gray-200') }}">
                    
                    <div class="flex items-center space-x-3">
                        <!-- Hamburger Button (Mobile Only) -->
                        <button @click="mobileMenuOpen = true" class="md:hidden p-2 rounded-lg hover:bg-gray-100/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        <div>
                            <h2 class="font-bold text-base md:text-xl tracking-tight leading-tight">
                                @if(request('brand') === 'markasnack')
                                    Kasir Markasnack
                                @elseif(request('brand') === 'glytch')
                                    Kasir Glytch
                                @else
                                    Dashboard Utama
                                @endif
                            </h2>
                            <p class="text-[11px] opacity-75 hidden sm:block">
                                @if(request('brand') === 'markasnack')
                                    Snack Prasmanan (Sistem Gramasi)
                                @elseif(request('brand') === 'glytch')
                                    Aksesoris & Retail (Sistem Pcs)
                                @else
                                    Pantau omzet dan aktivitas toko
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Mode Indicator Pill -->
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider text-[10px] shadow-sm
                            {{ request('brand') === 'markasnack' ? 'bg-red-900 text-red-100 border border-red-700' : (request('brand') === 'glytch' ? 'bg-neutral-800 text-white border border-neutral-700' : 'bg-gray-100 text-gray-700 border border-gray-300') }}">
                            {{ request('brand', 'default') }}
                        </span>
                    </div>
                </header>

                <!-- PAGE CONTENT AREA -->
                <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
                    {{ $slot }}
                </main>

            </div>

        </div>
    </body>
</html>
