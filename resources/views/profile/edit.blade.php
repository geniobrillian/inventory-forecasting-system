<x-app-layout>
    <x-slot name="header">
        Pengaturan Profil & Akun
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <!-- Card 1: Information Profile -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-1">Informasi Profil</h3>
            <p class="text-xs text-slate-400 mb-6">Perbarui informasi dasar profil dan alamat email akun Anda.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    @error('name')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('email')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nomor Telepon / WhatsApp</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                               placeholder="08123456789">
                        @error('phone')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl text-sm shadow-md transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Update Password -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-1">Ubah Kata Sandi</h3>
            <p class="text-xs text-slate-400 mb-6">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak demi keamanan.</p>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kata Sandi Saat Ini</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    @error('current_password')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('password')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl text-sm shadow-md transition">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
