@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Page Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Kartu Stok (Stock Card Ledger)</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Buku besar mutasi inventori terperinci dengan kalkulasi saldo awal & saldo berjalan (*running balance*)</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($selectedProduct)
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-800 bg-slate-900 text-slate-300 text-xs font-semibold hover:bg-slate-800 hover:text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Kartu Stok</span>
                </button>
            @endif
            <a href="{{ route('inventory.overview') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 text-xs font-semibold hover:bg-slate-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Stock Overview</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm shadow-xl">
        <form method="GET" action="{{ route('inventory.stock-card') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Product Selector -->
            <div class="lg:col-span-2">
                <label for="product_id" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    Pilih Produk <span class="text-rose-400">*</span>
                </label>
                <select name="product_id" id="product_id" required class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-3 py-2 text-xs text-slate-200 focus:border-indigo-500 outline-none">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $selectedProduct && $selectedProduct->id == $product->id ? 'selected' : '' }}>
                            [{{ $product->sku }}] {{ $product->name }} ({{ $product->unit->symbol ?? 'Unit' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Warehouse Selector -->
            <div>
                <label for="warehouse_id" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    Gudang
                </label>
                <select name="warehouse_id" id="warehouse_id" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-3 py-2 text-xs text-slate-200 focus:border-indigo-500 outline-none">
                    <option value="">Semua Gudang (Konsolidasi)</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouse && $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->code }}] {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Transaction Type Filter -->
            <div>
                <label for="type" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    Tipe Transaksi
                </label>
                <select name="type" id="type" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-3 py-2 text-xs text-slate-200 focus:border-indigo-500 outline-none">
                    <option value="">Semua Tipe</option>
                    <option value="PURCHASE" {{ request('type') === 'PURCHASE' ? 'selected' : '' }}>PURCHASE (Beli)</option>
                    <option value="SALE" {{ request('type') === 'SALE' ? 'selected' : '' }}>SALE (Jual)</option>
                    <option value="TRANSFER_IN" {{ request('type') === 'TRANSFER_IN' ? 'selected' : '' }}>TRANSFER IN</option>
                    <option value="TRANSFER_OUT" {{ request('type') === 'TRANSFER_OUT' ? 'selected' : '' }}>TRANSFER OUT</option>
                    <option value="ADJUSTMENT_IN" {{ request('type') === 'ADJUSTMENT_IN' ? 'selected' : '' }}>ADJUSTMENT IN</option>
                    <option value="ADJUSTMENT_OUT" {{ request('type') === 'ADJUSTMENT_OUT' ? 'selected' : '' }}>ADJUSTMENT OUT</option>
                    <option value="RETURN_IN" {{ request('type') === 'RETURN_IN' ? 'selected' : '' }}>RETURN IN</option>
                    <option value="RETURN_OUT" {{ request('type') === 'RETURN_OUT' ? 'selected' : '' }}>RETURN OUT</option>
                </select>
            </div>

            <!-- Date Range & Submit Button -->
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Rentang</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-2 py-2 text-[11px] text-slate-200 focus:border-indigo-500 outline-none">
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-2 py-2 text-[11px] text-slate-200 focus:border-indigo-500 outline-none">
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/30 border border-indigo-500/50 transition h-[36px] flex items-center justify-center">
                    Cari
                </button>
            </div>
        </form>
    </div>

    @if($selectedProduct)
        <!-- Product Header & Summary Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Product Info Badge -->
            <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm shadow-xl flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block mb-0.5">SKU: {{ $selectedProduct->sku }}</span>
                    <h3 class="font-bold text-white text-sm leading-snug">{{ $selectedProduct->name }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Kategori: {{ $selectedProduct->category->name ?? '-' }} • Satuan: {{ $selectedProduct->unit->symbol ?? '-' }}</p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Gudang: <strong class="text-slate-200">{{ $selectedWarehouse ? $selectedWarehouse->name : 'Semua Gudang' }}</strong></span>
                </div>
            </div>

            <!-- Initial Balance -->
            <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm shadow-xl flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block mb-0.5">Saldo Awal (Sebelum {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }})</span>
                    <h4 class="text-2xl font-black text-white tracking-tight">
                        {{ number_format($initialBalance, 0, ',', '.') }}
                    </h4>
                    <span class="text-[11px] font-medium text-slate-500">{{ $selectedProduct->unit->symbol ?? 'Unit' }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Total In & Out in Period -->
            <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm shadow-xl flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">Mutasi Periode Ini</span>
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-400">
                            Masuk: +{{ number_format($totalIn, 0, ',', '.') }} {{ $selectedProduct->unit->symbol }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-rose-400">
                            Keluar: -{{ number_format($totalOut, 0, ',', '.') }} {{ $selectedProduct->unit->symbol }}
                        </div>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
            </div>

            <!-- Final Running Balance -->
            <div class="p-4 rounded-2xl bg-gradient-to-tr from-indigo-900/80 to-indigo-950 border border-indigo-500/30 text-white shadow-xl flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-indigo-300 block mb-0.5">Saldo Akhir (Running Balance)</span>
                    <h4 class="text-2xl font-black text-indigo-200 tracking-tight">
                        {{ number_format($finalBalance, 0, ',', '.') }}
                    </h4>
                    <span class="text-[11px] font-medium text-indigo-300">{{ $selectedProduct->unit->symbol ?? 'Unit' }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-500/40 text-indigo-300 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/15 border border-indigo-500/30 text-indigo-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-xs uppercase tracking-wider">Rincian Buku Besar Mutasi Stok</h3>
                        <p class="text-[11px] text-slate-400">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                    {{ $transactions->count() }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-[10px] uppercase tracking-wider font-semibold text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">No. Referensi / ID</th>
                            <th class="py-3 px-4">Gudang</th>
                            <th class="py-3 px-4 text-center">Tipe Transaksi</th>
                            <th class="py-3 px-4 text-right">Masuk (+)</th>
                            <th class="py-3 px-4 text-right">Keluar (-)</th>
                            <th class="py-3 px-4 text-right">Saldo Berjalan</th>
                            <th class="py-3 px-4">Operator / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <!-- Initial Balance Row -->
                        <tr class="bg-slate-950/30 font-semibold text-slate-300">
                            <td class="py-3 px-4 text-slate-400">
                                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 italic font-mono">
                                [SALDO-AWAL]
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                {{ $selectedWarehouse ? $selectedWarehouse->name : 'Semua Gudang' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                    SALDO AWAL
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-500">-</td>
                            <td class="py-3 px-4 text-right text-slate-500">-</td>
                            <td class="py-3 px-4 text-right font-bold text-white">
                                {{ number_format($initialBalance, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 italic text-[11px]">
                                Saldo kumulatif sebelum periode filter
                            </td>
                        </tr>

                        @forelse($transactions as $tx)
                            <tr class="hover:bg-slate-800/20 transition">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-400">
                                    <div class="font-semibold text-slate-200">{{ $tx->transaction_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $tx->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-indigo-300">{{ $tx->reference_id ?? '-' }}</span>
                                    <div class="text-[10px] text-slate-500">TX#{{ $tx->id }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-300">
                                    {{ $tx->warehouse->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $typeClasses = match($tx->transaction_type) {
                                            'PURCHASE', 'RETURN_IN', 'ADJUSTMENT_IN', 'TRANSFER_IN', 'INITIAL' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                            'SALE', 'RETURN_OUT', 'ADJUSTMENT_OUT', 'TRANSFER_OUT' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                            default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $typeClasses }}">
                                        {{ $tx->transaction_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-400">
                                    {{ $tx->qty_in > 0 ? '+' . number_format($tx->qty_in, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-rose-400">
                                    {{ $tx->qty_out > 0 ? '-' . number_format($tx->qty_out, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-white text-sm">
                                    {{ number_format($tx->balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-slate-400">
                                    <div class="text-slate-200 font-medium">{{ $tx->notes ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">By: {{ $tx->user?->name ?? 'System' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-500 italic">
                                    Tidak ada mutasi transaksi pada rentang tanggal yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- No Product Selected Empty State -->
        <div class="p-12 rounded-2xl bg-slate-900/60 border border-slate-800/80 text-center shadow-xl backdrop-blur-sm">
            <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto mb-3 font-bold">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Pilih Produk Terlebih Dahulu</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">Silakan pilih produk pada filter di atas untuk melihat buku besar kartu stok dan kalkulasi mutasi saldo berjalan.</p>
        </div>
    @endif
</div>
@endsection
