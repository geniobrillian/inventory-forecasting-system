@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Page Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('inventory.overview') }}" class="text-xs font-semibold text-slate-400 hover:text-indigo-400 transition-colors uppercase tracking-wider">Inventori</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-slate-500 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Kartu Stok</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-white tracking-tight">Kartu Stok (Stock Card Ledger)</h1>
            <p class="text-sm text-slate-400 mt-0.5">Buku besar mutasi inventori terperinci dengan kalkulasi saldo berjalan (running balance) real-time.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.overview') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 text-sm font-semibold hover:bg-slate-700 hover:border-slate-600 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali ke Ringkasan</span>
            </a>
        </div>
    </div>
    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
        <form method="GET" action="{{ route('inventory.stock-card') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <!-- Product Selector -->
            <div class="md:col-span-2">
                <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Pilih Produk <span class="text-rose-500">*</span>
                </label>
                <select name="product_id" id="product_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $selectedProduct && $selectedProduct->id == $product->id ? 'selected' : '' }}>
                            [{{ $product->sku }}] {{ $product->name }} ({{ $product->unit->code ?? 'Unit' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Warehouse Selector -->
            <div>
                <label for="warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Lokasi Gudang
                </label>
                <select name="warehouse_id" id="warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                    <option value="">Semua Gudang (Konsolidasi)</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouse && $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->code }}] {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label for="start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Rentang Tanggal
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm hover:shadow-indigo-600/20 transition-all">
                    <i class="ki-filled ki-filter text-base"></i>
                    <span>Tampilkan</span>
                </button>
            </div>
        </form>
    </div>

    @if($selectedProduct)
        <!-- Product Header & Summary Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Product Info Badge -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider block mb-1">SKU: {{ $selectedProduct->sku }}</span>
                    <h3 class="font-bold text-slate-900 text-base leading-snug">{{ $selectedProduct->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Kategori: {{ $selectedProduct->category->name ?? '-' }} | Satuan: {{ $selectedProduct->unit->name ?? '-' }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Gudang: <strong class="text-slate-800">{{ $selectedWarehouse ? $selectedWarehouse->name : 'Semua Gudang' }}</strong></span>
                </div>
            </div>

            <!-- Initial Balance -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 block mb-1">Saldo Awal (Sebelum {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }})</span>
                    <h4 class="text-2xl font-black text-slate-800 tracking-tight">
                        {{ number_format($initialBalance, 2) }}
                    </h4>
                    <span class="text-xs font-medium text-slate-400">{{ $selectedProduct->unit->code ?? 'Unit' }}</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                    <i class="ki-filled ki-time text-2xl"></i>
                </div>
            </div>

            <!-- Total In & Out in Period -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 block mb-1">Mutasi Periode Ini</span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                            <i class="ki-filled ki-arrow-down text-emerald-500"></i> Masuk: +{{ number_format($totalIn, 2) }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-rose-600">
                            <i class="ki-filled ki-arrow-up text-rose-500"></i> Keluar: -{{ number_format($totalOut, 2) }}
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <i class="ki-filled ki-arrows-loop text-2xl"></i>
                </div>
            </div>

            <!-- Final Running Balance -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-indigo-200 block mb-1">Saldo Akhir (Running Balance)</span>
                    <h4 class="text-2xl font-black tracking-tight">
                        {{ number_format($finalBalance, 2) }}
                    </h4>
                    <span class="text-xs font-medium text-indigo-200">{{ $selectedProduct->unit->code ?? 'Unit' }}</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 text-white flex items-center justify-center font-bold">
                    <i class="ki-filled ki-wallet text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                        <i class="ki-filled ki-document text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Rincian Buku Besar Mutasi Stok</h3>
                        <p class="text-xs text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    Total {{ $transactions->count() }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/75 text-[11px] uppercase tracking-wider font-bold text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">No. Referensi / ID</th>
                            <th class="py-3 px-4">Gudang</th>
                            <th class="py-3 px-4">Tipe Transaksi</th>
                            <th class="py-3 px-4 text-right">Masuk (+)</th>
                            <th class="py-3 px-4 text-right">Keluar (-)</th>
                            <th class="py-3 px-4 text-right">Saldo Berjalan</th>
                            <th class="py-3 px-4">Operator / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Initial Balance Row -->
                        <tr class="bg-slate-50/40 font-semibold text-slate-700">
                            <td class="py-3 px-4 text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-400 italic">
                                [SALDO-AWAL]
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">
                                {{ $selectedWarehouse ? $selectedWarehouse->name : 'Semua Gudang' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-700">
                                    SALDO AWAL
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-xs text-slate-400">-</td>
                            <td class="py-3 px-4 text-right text-xs text-slate-400">-</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">
                                {{ number_format($initialBalance, 2) }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500 italic">
                                Saldo kumulatif sebelum periode filter
                            </td>
                        </tr>

                        @forelse($transactions as $tx)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 text-xs text-slate-700 whitespace-nowrap">
                                    <div class="font-semibold">{{ $tx->transaction_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="font-mono font-bold text-slate-800">{{ $tx->reference_id ?? '-' }}</span>
                                    <div class="text-[10px] text-slate-400">TX#{{ $tx->id }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-700">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ki-filled ki-home-2 text-slate-400 text-xs"></i>
                                        {{ $tx->warehouse->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if(in_array($tx->transaction_type, ['PURCHASE', 'RETURN_IN', 'ADJUSTMENT_IN', 'TRANSFER_IN']))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="ki-filled ki-arrow-down text-emerald-500"></i>
                                            {{ $tx->transaction_type }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="ki-filled ki-arrow-up text-rose-500"></i>
                                            {{ $tx->transaction_type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 text-xs">
                                    {{ $tx->qty_in > 0 ? '+' . number_format($tx->qty_in, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-rose-600 text-xs">
                                    {{ $tx->qty_out > 0 ? '-' . number_format($tx->qty_out, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900 text-sm">
                                    {{ number_format($tx->balance, 2) }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-500">
                                    <div class="text-slate-700 font-medium">{{ $tx->notes ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                        <i class="ki-filled ki-user text-[10px]"></i>
                                        {{ $tx->performer->name ?? 'System' }}
                                    </div>
                                </td>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs italic">
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
        <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 font-bold">
                <i class="ki-filled ki-document text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Pilih Produk Terlebih Dahulu</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">Silakan pilih produk pada filter di atas untuk melihat buku besar kartu stok dan kalkulasi mutasi saldo berjalan.</p>
        </div>
    @endif
</div>
@endsection
