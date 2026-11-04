<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OmniPOS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-800 antialiased flex items-center justify-center min-h-screen px-4">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 space-y-6 border border-slate-100">
        
        <!-- Header Logo Markasnack x Glytch -->
        <div class="text-center space-y-3">
            <div class="flex items-center justify-center gap-4">
                <!-- Logo Markasnack (Diperbesar ke h-16 w-16 / 64px) -->
                <img src="/images/markasnack.png" 
                     class="h-16 w-16 rounded-full object-cover border-2 border-rose-200 shadow-md" 
                     onerror="this.style.display='none'">

                <!-- Tanda Perkalian Pemisah -->
                <span class="text-slate-300 font-light text-2xl">×</span>

                <!-- Logo Glytch (Diperbesar ke h-12 / 48px) -->
                <img src="/images/glytch.png" 
                     class="h-12 object-contain" 
                     onerror="this.style.display='none'">
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Welcome to OmniPOS</h1>
                <p class="text-xs text-slate-400 font-medium">Markasnack x Glytch Management System</p>
            </div>
        </div>

        @if($errors->any())
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-600 font-semibold text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="admin@omnipos.com" 
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                Sign In to Dashboard
            </button>
        </form>

        <div class="text-center text-[10px] text-slate-400 pt-2 border-t border-slate-100">
            Protected Multi-Store POS Environment
        </div>
    </div>

</body>
</html>