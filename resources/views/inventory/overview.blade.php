@extends('layouts.app', ['title' => 'Ringkasan Stok Persediaan'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-200">Inventory Core</span>
                <span>/</span>
                <span class="text-indigo-400 font-medium">Stock Overview</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Ringkasan Inventori & Stok Persediaan</h1>
            <p class="text-sm text-slate-400">Monitoring saldo stok multi-gudang real-time, status buffer stok, dan valuasi inventaris.</p>
        </div>

        <!-- Quick Action Operation Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inventory.stock-in') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Stock In (+)</span>
            </a>

            <a href="{{ route('inventory.stock-out') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
                <span>Stock Out (-)</span>
            </a>

            <a href="{{ route('inventory.transfer') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Transfer Antar-Gudang</span>
            </a>

            <a href="{{ route('inventory.adjustment') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-amber-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Stock Opname</span>
            </a>

            <a href="{{ route('inventory.stock-card') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/80 transition">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Kartu Stok</span>
            </a>
        </div>
    </div>

    <!-- Inventory KPI Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Units -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Total Kuantitas Fisik</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total_units']) }} <span class="text-xs font-normal text-slate-400">Unit</span></h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Total Inventory Valuation -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Total Valuasi Aset Stok</p>
                <h3 class="text-2xl font-bold text-emerald-400 mt-1">Rp {{ number_format($stats['total_valuation'], 0, ',', '.') }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Stok Menipis (&le; Min)</p>
                <h3 class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($stats['low_stock']) }} <span class="text-xs font-normal text-slate-400">SKU</span></h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Out of Stock Items -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Stok Habis (0 Unit)</p>
                <h3 class="text-2xl font-bold text-rose-400 mt-1">{{ number_format($stats['out_of_stock']) }} <span class="text-xs font-normal text-slate-400">SKU</span></h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="p-4 bg-slate-900/70 border border-slate-800/80 rounded-2xl backdrop-blur-sm">
        <form method="GET" action="{{ route('inventory.overview') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search SKU or Name -->
            <div class="lg:col-span-2 relative">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari SKU atau nama produk..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Filter Category -->
            <div>
                <select name="category_id" class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Warehouse -->
            <div>
                <select name="warehouse_id" class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} ({{ $wh->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'category_id', 'warehouse_id']))
                    <a href="{{ route('inventory.overview') }}" class="px-3 py-2 text-slate-400 hover:text-white text-sm transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Inventory Stock Table Card -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="px-6 py-3.5">SKU & Nama Produk</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Total Stok</th>
                        <th class="px-6 py-3.5">Rincian Per Gudang</th>
                        <th class="px-6 py-3.5">Status Kesehatan</th>
                        <th class="px-6 py-3.5">Valuasi Stok (IDR)</th>
                        <th class="px-6 py-3.5 text-right">Aksi Operasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($products as $product)
                        @php
                            $totalStock = $product->total_stock;
                            $minStock = $product->minimum_stock;
                            $valuation = $totalStock * $product->purchase_price;

                            $status = 'HEALTHY';
                            if ($totalStock <= 0) {
                                $status = 'OUT_OF_STOCK';
                            } elseif ($totalStock <= $minStock) {
                                $status = 'LOW_STOCK';
                            }
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Product SKU & Name -->
                            <td class="px-6 py-4">
                                <a href="{{ route('products.show', $product) }}" class="font-bold text-white text-base hover:text-indigo-400 transition">
                                    {{ $product->name }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 bg-indigo-500/10 border border-indigo-500/30 rounded text-[11px] font-mono font-bold text-indigo-400">
                                        {{ $product->sku }}
                                    </span>
                                    <span class="text-xs text-slate-400">UoM: {{ $product->unit?->code ?? '-' }}</span>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-slate-800 rounded-lg text-xs text-slate-300">
                                    {{ $product->category?->name ?? '-' }}
                                </span>
                            </td>

                            <!-- Total Stock -->
                            <td class="px-6 py-4">
                                <div class="text-base font-bold font-mono text-white">
                                    {{ number_format($totalStock) }} <span class="text-xs font-normal text-slate-400">{{ $product->unit?->code ?? 'Unit' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Min Buffer: {{ number_format($minStock) }}</div>
                            </td>

                            <!-- Per-Warehouse Stock Breakdown -->
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @forelse($product->inventoryStocks as $stock)
                                        @if($stock->quantity > 0)
                                            <div class="flex items-center justify-between gap-3 text-xs bg-slate-950/50 px-2.5 py-1 rounded-lg border border-slate-800/80">
                                                <span class="text-slate-300 truncate font-medium">{{ $stock->warehouse?->name ?? 'Gudang' }}</span>
                                                <span class="font-mono font-bold text-indigo-300">{{ number_format($stock->quantity) }}</span>
                                            </div>
                                        @endif
                                    @empty
                                        <span class="text-xs text-slate-500 italic">Belum ada alokasi stok</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Stock Status Badge -->
                            <td class="px-6 py-4">
                                @if($status === 'HEALTHY')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Stok Aman
                                    </span>
                                @elseif($status === 'LOW_STOCK')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Stok Menipis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Habis (0)
                                    </span>
                                @endif
                            </td>

                            <!-- Valuation -->
                            <td class="px-6 py-4 font-mono font-semibold text-emerald-400">
                                Rp {{ number_format($valuation, 0, ',', '.') }}
                            </td>

                            <!-- Action Operation Buttons -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('inventory.stock-card', ['product_id' => $product->id]) }}"
                                       class="px-2.5 py-1.5 bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white rounded-lg text-xs font-semibold transition"
                                       title="Lihat Kartu Stok Lengkap">
                                        Kartu Stok &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="text-base font-semibold text-slate-400">Tidak ada produk ditemukan</p>
                                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-950/40">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
