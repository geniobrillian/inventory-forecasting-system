@extends('layouts.app', ['title' => 'Tambah Kategori'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
            <span>/</span>
            <a href="{{ route('categories.index') }}" class="hover:text-slate-200 transition">Kategori</a>
            <span>/</span>
            <span class="text-indigo-400 font-medium">Tambah Baru</span>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Tambah Kategori Baru</h1>
        <p class="text-sm text-slate-400">Buat klasifikasi kategori baru untuk mengelompokkan barang atau produk inventaris.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm shadow-xl">
        <form action="{{ route('categories.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Category Name -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Nama Kategori <span class="text-rose-400">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name') }}"
                       required
                       placeholder="Contoh: Elektronik & Komputer"
                       class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('name') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                @error('name')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug (Optional) -->
            <div>
                <label for="slug" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Slug / URL Identifier <span class="text-slate-500 text-xs normal-case font-normal">(Opsional - otomatis dibuat dari nama jika kosong)</span>
                </label>
                <input type="text"
                       name="slug"
                       id="slug"
                       value="{{ old('slug') }}"
                       placeholder="elektronik-dan-komputer"
                       class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('slug') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                @error('slug')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Deskripsi Kategori <span class="text-slate-500 text-xs normal-case font-normal">(Opsional)</span>
                </label>
                <textarea name="description"
                          id="description"
                          rows="4"
                          placeholder="Penjelasan singkat mengenai tipe produk di dalam kategori ini..."
                          class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('description') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status Checkbox -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    <span class="text-sm font-medium text-slate-300">Aktifkan kategori ini agar dapat dipilih pada produk baru</span>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800/80">
                <a href="{{ route('categories.index') }}"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
