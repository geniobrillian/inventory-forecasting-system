@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Sales Orders (Penjualan)</h1>
                    <p class="text-xs text-slate-400">Pencatatan transaksi penjualan barang keluar dan penerbitan faktur</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('sales.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/30 border border-indigo-500/50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Transaksi Penjualan Baru</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Cari No. Faktur / Pelanggan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: INV-202609... / Nama"
                       class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Status Penjualan</label>
                <select name="status" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Selesai)</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED (Dibatalkan)</option>
                </select>
            </div>

            <!-- Warehouse -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Gudang Asal</label>
                <select name="warehouse_id" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('sales.index') }}" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-medium rounded-xl border border-slate-800 transition" title="Reset">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800/80 overflow-hidden backdrop-blur-sm shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Faktur & Tanggal</th>
                        <th class="px-4 py-3.5">Pelanggan</th>
                        <th class="px-4 py-3.5">Gudang</th>
                        <th class="px-4 py-3.5 text-right">Total Item</th>
                        <th class="px-4 py-3.5 text-right">Total Nominal</th>
                        <th class="px-4 py-3.5 text-center">Pembayaran</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3.5">
                                <a href="{{ route('sales.show', $sale) }}" class="font-bold text-indigo-400 hover:text-indigo-300 block">
                                    {{ $sale->invoice_number }}
                                </a>
                                <span class="text-[11px] text-slate-500">
                                    {{ $sale->sale_date ? $sale->sale_date->format('d M Y') : '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-medium text-slate-200 block">{{ $sale->customer_name ?: 'Pelanggan Umum' }}</span>
                                @if($sale->customer_phone)
                                    <span class="text-[11px] text-slate-500">{{ $sale->customer_phone }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-300">
                                {{ $sale->warehouse?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-slate-300">
                                {{ number_format($sale->items->sum('quantity'), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-slate-100">
                                Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $payClasses = match($sale->payment_status) {
                                        'PAID' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                        'PARTIAL' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                        'UNPAID' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                        default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $payClasses }}">
                                    {{ $sale->payment_status }}
                                </span>
                                <span class="block text-[10px] text-slate-500 mt-0.5">{{ $sale->payment_method }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $statusClasses = match($sale->status) {
                                        'DRAFT' => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                        'COMPLETED' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                        'CANCELLED' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                        default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                                    {{ $sale->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                <a href="{{ route('sales.show', $sale) }}" class="p-1.5 inline-flex items-center text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition" title="Lihat Faktur">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                @if($sale->isDraft())
                                    <a href="{{ route('sales.edit', $sale) }}" class="p-1.5 inline-flex items-center text-amber-400 hover:text-amber-300 rounded-lg hover:bg-amber-500/10 transition" title="Edit Draf">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data penjualan / faktur ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
