<x-guest-layout>
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 shadow-2xl rounded-2xl p-6 sm:p-8" x-data="{ email: '', password: '' }">
        <h2 class="text-xl font-semibold text-white mb-2">Masuk ke Sistem</h2>
        <p class="text-sm text-slate-400 mb-6">Masukkan kredensial Anda untuk melanjutkan ke dashboard.</p>

        <!-- Session Status / Errors -->
        @if (session('info'))
            <div class="mb-5 p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-300 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
                <div class="font-medium mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" x-model="email" required autofocus autocomplete="username"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-900/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="nama@perusahaan.com">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" type="password" name="password" x-model="password" required autocomplete="current-password"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-900/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="••••••••">
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember" class="flex items-center gap-2 cursor-pointer">
                    <input id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    <span class="text-xs text-slate-400 select-none">Ingat saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                        class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-medium rounded-xl text-sm shadow-lg shadow-indigo-600/30 transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <span>Masuk</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Quick Demo Login Switcher -->
        <div class="mt-6 pt-5 border-t border-slate-700/60">
            <p class="text-xs text-slate-400 font-medium mb-2.5 text-center">Akun Cepat (Development / Demo):</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" @click="email = 'admin@inventory.local'; password = 'password'"
                        class="p-2 bg-slate-900/60 hover:bg-slate-700/60 border border-slate-700/60 rounded-lg text-left transition text-slate-300 hover:text-white">
                    <div class="font-semibold text-indigo-400">Super Admin</div>
                    <div class="text-[10px] text-slate-500 truncate">admin@inventory.local</div>
                </button>
                <button type="button" @click="email = 'warehouse@inventory.local'; password = 'password'"
                        class="p-2 bg-slate-900/60 hover:bg-slate-700/60 border border-slate-700/60 rounded-lg text-left transition text-slate-300 hover:text-white">
                    <div class="font-semibold text-emerald-400">Warehouse Staff</div>
                    <div class="text-[10px] text-slate-500 truncate">warehouse@inventory.local</div>
                </button>
                <button type="button" @click="email = 'purchasing@inventory.local'; password = 'password'"
                        class="p-2 bg-slate-900/60 hover:bg-slate-700/60 border border-slate-700/60 rounded-lg text-left transition text-slate-300 hover:text-white">
                    <div class="font-semibold text-sky-400">Purchasing Staff</div>
                    <div class="text-[10px] text-slate-500 truncate">purchasing@inventory.local</div>
                </button>
                <button type="button" @click="email = 'manager@inventory.local'; password = 'password'"
                        class="p-2 bg-slate-900/60 hover:bg-slate-700/60 border border-slate-700/60 rounded-lg text-left transition text-slate-300 hover:text-white">
                    <div class="font-semibold text-amber-400">Manager</div>
                    <div class="text-[10px] text-slate-500 truncate">manager@inventory.local</div>
                </button>
            </div>
        </div>
    </div>
</x-guest-layout>
