@extends('layouts.app', ['title' => 'Katalog Produk'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-200">Master Data</span>
                <span>/</span>
                <span class="text-indigo-400 font-medium">Produk</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Katalog Master Produk</h1>
            <p class="text-sm text-slate-400">Master data SKU barang, parameter lead time, ambang batas stok minimum, dan metode peramalan.</p>
        </div>

        <a href="{{ route('products.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Produk Baru</span>
        </a>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Total Produk</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total']) }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Produk Aktif</p>
                <h3 class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($stats['active']) }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Nonaktif</p>
                <h3 class="text-2xl font-bold text-slate-400 mt-1">{{ number_format($stats['inactive']) }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700/80 flex items-center justify-center text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Rata-rata Margin</p>
                <h3 class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($stats['avg_margin'], 1) }}%</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="p-4 bg-slate-900/70 border border-slate-800/80 rounded-2xl backdrop-blur-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
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
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Supplier -->
            <div>
                <select name="supplier_id" class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Pemasok</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Filter Submit & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'category_id', 'supplier_id', 'status', 'forecast_method']))
                    <a href="{{ route('products.index') }}" class="px-3 py-2 text-slate-400 hover:text-white text-sm transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table Card -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="px-6 py-3.5">SKU & Nama Produk</th>
                        <th class="px-6 py-3.5">Kategori & Satuan</th>
                        <th class="px-6 py-3.5">Pemasok</th>
                        <th class="px-6 py-3.5">Harga (Beli / Jual)</th>
                        <th class="px-6 py-3.5">Min. Stok / Lead Time</th>
                        <th class="px-6 py-3.5">Forecast Method</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- SKU & Name -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-base">
                                    <a href="{{ route('products.show', $product) }}" class="hover:text-indigo-400 transition">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <span class="inline-block mt-1 px-2.5 py-0.5 bg-indigo-500/10 border border-indigo-500/30 rounded-lg text-xs font-mono font-bold text-indigo-400">
                                    {{ $product->sku }}
                                </span>
                            </td>

                            <!-- Category & Unit -->
                            <td class="px-6 py-4">
                                <div class="text-slate-200 font-medium">{{ $product->category?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400 font-mono">UoM: {{ $product->unit?->code ?? '-' }}</div>
                            </td>

                            <!-- Supplier -->
                            <td class="px-6 py-4">
                                <div class="text-slate-300 text-xs font-semibold">{{ $product->supplier?->name ?? '-' }}</div>
                                @if($product->supplier)
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $product->supplier->code }}</div>
                                @endif
                            </td>

                            <!-- Pricing -->
                            <td class="px-6 py-4">
                                <div class="text-xs font-mono text-emerald-400 font-semibold">
                                    Jual: Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </div>
                                <div class="text-xs font-mono text-slate-400">
                                    Beli: Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Stock threshold & Lead time -->
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-300">Min: <strong class="text-white">{{ number_format($product->minimum_stock) }}</strong> {{ $product->unit?->code ?? '' }}</div>
                                <div class="text-xs text-amber-400 font-medium">LT: {{ $product->lead_time_days }} Hari</div>
                            </td>

                            <!-- Forecast Method -->
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold {{ $product->forecast_method === 'MOVING_AVERAGE' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border border-purple-500/20' }}">
                                    {{ $product->forecast_method }}
                                </span>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-6 py-4">
                                <form action="{{ route('products.toggle-status', $product) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="Klik untuk mengubah status"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('products.show', $product) }}"
                                       class="p-1.5 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded-lg transition"
                                       title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('products.edit', $product) }}"
                                       class="p-1.5 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded-lg transition"
                                       title="Edit Produk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <form action="{{ route('products.destroy', $product) }}"
                                          method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }} (SKU: {{ $product->sku }})?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded-lg transition"
                                                title="Hapus Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="text-base font-semibold text-slate-400">Tidak ada produk ditemukan</p>
                                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau tambahkan produk baru.</p>
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
