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

    <div class="flex h-screen w-screen overflow-hidden">

        <!-- SIDEBAR TAILADMIN -->
        <aside class="z-50 flex h-full flex-col bg-slate-900 transition-all duration-300 ease-in-out shrink-0 select-none overflow-hidden"
               :class="sidebarOpen ? 'w-64' : 'w-20'">

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
                            <a href="{{ route('stores.index') }}"
                               title="Manajemen Toko"
                               class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium text-slate-300 transition-colors duration-150 hover:bg-slate-800 hover:text-white {{ request()->routeIs('stores.*') ? 'bg-slate-800 text-white font-semibold' : '' }}">
                                <svg class="w-5 h-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 4h4m-4-8h4m-4 4h4"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-sm">Manajemen Toko</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index') }}"
                               title="Produk & Stok FIFO"
                               class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium text-slate-300 transition-colors duration-150 hover:bg-slate-800 hover:text-white {{ request()->routeIs('products.*') ? 'bg-slate-800 text-white font-semibold' : '' }}">
                                <svg class="w-5 h-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-sm">Produk & Stok FIFO</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('categories.index') }}"
                               title="Manajemen Kategori"
                               class="flex items-center gap-3.5 rounded-xl px-3.5 py-3 font-medium text-slate-300 transition-colors duration-150 hover:bg-slate-800 hover:text-white {{ request()->routeIs('categories.*') ? 'bg-slate-800 text-white font-semibold' : '' }}">
                                <svg class="w-5 h-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap overflow-hidden text-sm">Manajemen Kategori</span>
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
                            class="p-2 border border-slate-200 rounded-xl hover:bg-slate-100 transition text-slate-600 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-800">Back-Office System</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 font-medium text-xs rounded-full border border-emerald-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Mode Pengembang
                    </span>
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
