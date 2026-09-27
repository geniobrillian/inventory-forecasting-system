@extends('layouts.app', ['title' => 'Detail Produk ' . $product->name])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-slate-200 transition">Produk</a>
                <span>/</span>
                <span class="text-indigo-400 font-medium">{{ $product->sku }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $product->name }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <p class="text-sm font-mono text-indigo-400 mt-0.5">SKU: {{ $product->sku }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('products.edit', $product) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Produk</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                <span>Katalog Produk</span>
            </a>
        </div>
    </div>

    <!-- Product Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Classification Card -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Klasifikasi & Mitra
            </h3>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Kategori Produk</span>
                    <span class="font-semibold text-slate-200">{{ $product->category?->name ?? 'Tanpa Kategori' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Satuan Unit (UoM)</span>
                    <span class="font-semibold text-slate-200">{{ $product->unit?->name ?? '-' }} ({{ $product->unit?->code ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Supplier Utama</span>
                    @if($product->supplier)
                        <a href="{{ route('suppliers.show', $product->supplier) }}" class="font-semibold text-indigo-400 hover:text-indigo-300 transition">
                            {{ $product->supplier->name }} ({{ $product->supplier->code }})
                        </a>
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pricing & Margin Card -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Finansial & Margin
            </h3>

            <div class="space-y-3">
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 block">Harga Beli Pokok</span>
                        <span class="text-base font-mono font-bold text-slate-200">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block text-right">Harga Jual</span>
                        <span class="text-base font-mono font-bold text-emerald-400">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                    <span class="text-xs text-slate-400">Gross Profit Margin</span>
                    <span class="text-base font-bold text-amber-400">{{ number_format($profitMargin, 1) }}%</span>
                </div>
            </div>
        </div>

        <!-- Forecasting & Inventory Parameters -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Parameter Restock & Forecast
            </h3>

            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Minimum Buffer Stok:</span>
                    <span class="font-bold text-white">{{ number_format($product->minimum_stock) }} {{ $product->unit?->code ?? 'Unit' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Lead Time Supplier:</span>
                    <span class="font-bold text-amber-400">{{ $product->lead_time_days }} Hari</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Metode Forecast Default:</span>
                    <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold {{ $product->forecast_method === 'MOVING_AVERAGE' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border border-purple-500/20' }}">
                        {{ $product->forecast_method }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory State Placeholder (Phase 3 Preparation) -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4 mb-4">
            <div>
                <h3 class="text-base font-bold text-white">Status Stok Inventaris Antar Gudang</h3>
                <p class="text-xs text-slate-400">Pencatatan persediaan fisik per gudang (akan terhubung pada Phase 3 Inventory Core).</p>
            </div>
            <span class="px-2.5 py-1 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold rounded-lg">
                Siap untuk Phase 3
            </span>
        </div>

        <div class="p-8 text-center bg-slate-950/40 rounded-xl border border-dashed border-slate-800">
            <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p class="text-sm font-semibold text-slate-300">Data Master Produk Telah Terintegrasi</p>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                Model produk ini siap menerima transaksi penerimaan barang (Stock In), pengeluaran (Stock Out), dan mutasi kartu stok pada modul Inventory Core.
            </p>
        </div>
    </div>
</div>
@endsection
