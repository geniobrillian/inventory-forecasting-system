@extends('layouts.app', ['title' => 'Edit Supplier'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
            <span>/</span>
            <a href="{{ route('suppliers.index') }}" class="hover:text-slate-200 transition">Pemasok</a>
            <span>/</span>
            <span class="text-indigo-400 font-medium">Edit #{{ $supplier->id }}</span>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Edit Supplier: {{ $supplier->name }}</h1>
        <p class="text-sm text-slate-400">Perbarui profil supplier, kontak PIC, dan parameter lead time.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm shadow-xl">
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Supplier Code -->
                <div>
                    <label for="code" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Kode Supplier <span class="text-rose-400">*</span>
                    </label>
                    <input type="text"
                           name="code"
                           id="code"
                           value="{{ old('code', $supplier->code) }}"
                           required
                           class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('code') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono uppercase text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    @error('code')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Supplier Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nama Perusahaan / Supplier <span class="text-rose-400">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $supplier->name) }}"
                           required
                           class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('name') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    @error('name')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Contact Person -->
                <div>
                    <label for="contact_person" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Kontak Person (PIC)
                    </label>
                    <input type="text"
                           name="contact_person"
                           id="contact_person"
                           value="{{ old('contact_person', $supplier->contact_person) }}"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('contact_person') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    @error('contact_person')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Default Lead Time -->
                <div>
                    <label for="default_lead_time_days" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Default Lead Time (Hari) <span class="text-rose-400">*</span>
                    </label>
                    <input type="number"
                           name="default_lead_time_days"
                           id="default_lead_time_days"
                           value="{{ old('default_lead_time_days', $supplier->default_lead_time_days) }}"
                           min="0"
                           max="365"
                           required
                           class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('default_lead_time_days') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    @error('default_lead_time_days')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           value="{{ old('phone', $supplier->phone) }}"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('phone') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    @error('phone')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Email Perusahaan
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', $supplier->email) }}"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('email') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    @error('email')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Alamat Lengkap Kantor / Pabrik
                </label>
                <textarea name="address"
                          id="address"
                          rows="3"
                          class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('address') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">{{ old('address', $supplier->address) }}</textarea>
                @error('address')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status Checkbox -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    <span class="text-sm font-medium text-slate-300">Supplier aktif</span>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800/80">
                <a href="{{ route('suppliers.show', $supplier) }}"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
