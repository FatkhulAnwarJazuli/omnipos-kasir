<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniPOS - Store Management</title>
    <!-- Tailwind & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Sembunyikan scrollbar bawaan browser agar tampilan sleek */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-100 text-slate-600 font-normal text-sm antialiased overflow-hidden" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen w-screen overflow-hidden relative">

        <!-- MOBILE BACKDROP -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm md:hidden" x-transition.opacity></div>

        <!-- SIDEBAR TAILADMIN -->
        <aside class="absolute md:relative z-50 flex h-full flex-col bg-slate-900 transition-all duration-300 ease-in-out shrink-0 select-none overflow-hidden border-r border-slate-800"
               :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-20'">

            <!-- Sidebar Header -->
            <div class="flex items-center h-20 px-5 border-b border-slate-800 shrink-0">
                <a href="{{ route('stores.index') }}" class="flex items-center gap-3.5 overflow-hidden">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white text-xl shadow-lg shadow-indigo-500/30">
                        O
                    </div>
                    <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap overflow-hidden">
                        <span class="text-white text-base font-bold tracking-wide block leading-none">OMNIPOS</span>
                        <span class="text-[10px] text-indigo-400 font-semibold tracking-wider uppercase">Multi-Store App</span>
                    </div>
                </a>
            </div>

            <!-- Sidebar Menu -->
            <div class="no-scrollbar flex-1 overflow-y-auto py-6 px-3 space-y-6">
                <div>
                    <h3 x-show="sidebarOpen" x-transition.opacity class="mb-3 px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                        Navigasi Utama
                    </h3>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('dashboard') }}" title="Dashboard Overview"
                                class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Dashboard Overview</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('stores.index') }}" title="Manajemen Toko"
                               class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('stores.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('stores.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 4h4m-4-8h4m-4 4h4"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Manajemen Toko</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index') }}" title="Produk & Stok FIFO"
                               class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('products.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Produk & Stok FIFO</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('categories.index') }}" title="Manajemen Kategori"
                               class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Manajemen Kategori</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('expenses.index') }}" title="Pengeluaran Operasional"
                                class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('expenses.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Pengeluaran Operasional</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('suppliers.index') }}" title="Supplier / Pemasok"
                                class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('suppliers.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Supplier / Pemasok</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('discounts.index') }}" title="Diskon & Promo"
                                class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('discounts.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('discounts.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Diskon & Promo</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pos.index') }}" title="Layar Kasir (POS)"
                                class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('pos.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('pos.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Layar Kasir (POS)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.index') }}" title="Laporan Penjualan & Laba"
                             class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium transition-colors duration-150 {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-xs font-bold">Laporan Penjualan & Laba</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- CONTENT AREA -->
        <div class="flex flex-1 flex-col h-full w-full overflow-hidden">

            <!-- TailAdmin Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Tombol Toggle Sidebar -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="p-2 border border-slate-200 rounded-xl hover:bg-slate-100 transition text-slate-600 focus:outline-none cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-800">Back-Office System</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 font-medium text-xs rounded-full border border-emerald-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Mode Pengembang
                    </span>

                    <!-- Tombol Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition cursor-pointer border border-rose-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Main Body (Hanya area ini yang scrollable) -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8 w-full">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>

    </div>
</body>
</html>