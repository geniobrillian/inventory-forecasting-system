@extends('layouts.app', ['title' => 'Tambah Produk Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-slate-200 transition">Produk</a>
            <span>/</span>
            <span class="text-indigo-400 font-medium">Tambah Baru</span>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Tambah Master Produk Baru</h1>
        <p class="text-sm text-slate-400">Daftarkan item barang baru ke dalam katalog master sistem persediaan.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm shadow-xl">
        <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Basic Identity -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Informasi Identitas Produk
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- SKU -->
                    <div>
                        <label for="sku" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            SKU (Stock Keeping Unit) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               name="sku"
                               id="sku"
                               value="{{ old('sku') }}"
                               required
                               placeholder="Contoh: PRD-ELK-001"
                               class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('sku') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono uppercase text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        @error('sku')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Name -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Nama Produk Lengkap <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="Contoh: Monitor LED 24 Inch Full HD IPS"
                               class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('name') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        @error('name')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Kategori Produk
                        </label>
                        <select name="category_id" id="category_id" class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('category_id') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unit -->
                    <div>
                        <label for="unit_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Satuan Unit (UoM)
                        </label>
                        <select name="unit_id" id="unit_id" class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('unit_id') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label for="supplier_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Pemasok Utama (Supplier)
                        </label>
                        <select name="supplier_id" id="supplier_id" class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('supplier_id') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Pricing & Inventory Thresholds -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-400 border-b border-slate-800 pb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Harga & Batas Inventaris
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Purchase Price -->
                    <div>
                        <label for="purchase_price" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Harga Beli Pokok (IDR) <span class="text-rose-400">*</span>
                        </label>
                        <input type="number"
                               step="0.01"
                               name="purchase_price"
                               id="purchase_price"
                               value="{{ old('purchase_price', 0) }}"
                               min="0"
                               required
                               class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('purchase_price') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                        @error('purchase_price')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <label for="selling_price" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Harga Jual (IDR) <span class="text-rose-400">*</span>
                        </label>
                        <input type="number"
                               step="0.01"
                               name="selling_price"
                               id="selling_price"
                               value="{{ old('selling_price', 0) }}"
                               min="0"
                               required
                               class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('selling_price') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                        @error('selling_price')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Minimum Stock -->
                    <div>
                        <label for="minimum_stock" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Minimum Stok <span class="text-rose-400">*</span>
                        </label>
                        <input type="number"
                               name="minimum_stock"
                               id="minimum_stock"
                               value="{{ old('minimum_stock', 10) }}"
                               min="0"
                               required
                               class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('minimum_stock') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                        @error('minimum_stock')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lead Time Days -->
                    <div>
                        <label for="lead_time_days" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Lead Time (Hari) <span class="text-rose-400">*</span>
                        </label>
                        <input type="number"
                               name="lead_time_days"
                               id="lead_time_days"
                               value="{{ old('lead_time_days', 7) }}"
                               min="0"
                               max="365"
                               required
                               class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('lead_time_days') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm font-mono text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                        @error('lead_time_days')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Forecasting & Status -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 border-b border-slate-800 pb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Konfigurasi Peramalan & Status
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="forecast_method" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Metode Peramalan Permintaan <span class="text-rose-400">*</span>
                        </label>
                        <select name="forecast_method" id="forecast_method" required class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $errors->has('forecast_method') ? 'border-rose-500' : 'border-slate-800' }} rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                            <option value="MOVING_AVERAGE" {{ old('forecast_method') === 'MOVING_AVERAGE' ? 'selected' : '' }}>Moving Average (MA)</option>
                            <option value="EXPONENTIAL_SMOOTHING" {{ old('forecast_method') === 'EXPONENTIAL_SMOOTHING' ? 'selected' : '' }}>Single Exponential Smoothing (SES)</option>
                        </select>
                        @error('forecast_method')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                            <span class="text-sm font-medium text-slate-300">Aktifkan produk untuk transaksi persediaan</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800/80">
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
