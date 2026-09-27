@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Purchase Orders (Pengadaan)</h1>
                    <p class="text-xs text-slate-400">Kelola pesanan pembelian barang ke pemasok dan penerimaan gudang</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/30 border border-indigo-500/50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Purchase Order</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
        <form method="GET" action="{{ route('purchasing.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Cari No. PO</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: PO-202609..."
                       class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Status</label>
                <select name="status" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="ORDERED" {{ request('status') === 'ORDERED' ? 'selected' : '' }}>ORDERED (Dipesan)</option>
                    <option value="PARTIALLY_RECEIVED" {{ request('status') === 'PARTIALLY_RECEIVED' ? 'selected' : '' }}>PARTIALLY RECEIVED</option>
                    <option value="RECEIVED" {{ request('status') === 'RECEIVED' ? 'selected' : '' }}>RECEIVED (Selesai)</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED (Dibatalkan)</option>
                </select>
            </div>

            <!-- Supplier -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Warehouse -->
            <div>
                <label class="block text-[11px] font-medium text-slate-400 mb-1">Gudang Tujuan</label>
                <select name="warehouse_id" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('purchasing.index') }}" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-medium rounded-xl border border-slate-800 transition" title="Reset">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800/80 overflow-hidden backdrop-blur-sm shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">No. PO & Tanggal</th>
                        <th class="px-4 py-3.5">Supplier</th>
                        <th class="px-4 py-3.5">Gudang Tujuan</th>
                        <th class="px-4 py-3.5 text-right">Total Item</th>
                        <th class="px-4 py-3.5 text-right">Total Nominal</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Progres Penerimaan</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($purchases as $po)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3.5">
                                <a href="{{ route('purchasing.show', $po) }}" class="font-bold text-indigo-400 hover:text-indigo-300 block">
                                    {{ $po->purchase_number }}
                                </a>
                                <span class="text-[11px] text-slate-500">
                                    {{ $po->order_date ? $po->order_date->format('d M Y') : '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-slate-200">
                                {{ $po->supplier?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-300">
                                {{ $po->warehouse?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-slate-300">
                                {{ number_format($po->items->sum('quantity'), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-slate-100">
                                Rp {{ number_format($po->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $statusClasses = match($po->status) {
                                        'DRAFT' => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                        'ORDERED' => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
                                        'PARTIALLY_RECEIVED' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                        'RECEIVED' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                        'CANCELLED' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                        default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden max-w-[100px] mx-auto border border-slate-700/50">
                                    <div class="bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width: {{ $po->receiving_progress_percent }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-1">{{ $po->receiving_progress_percent }}%</span>
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                <a href="{{ route('purchasing.show', $po) }}" class="p-1.5 inline-flex items-center text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                @if($po->canReceive())
                                    <a href="{{ route('purchasing.receive', $po) }}" class="p-1.5 inline-flex items-center text-emerald-400 hover:text-emerald-300 rounded-lg hover:bg-emerald-500/10 transition" title="Penerimaan Barang (GRN)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </a>
                                @endif

                                @if($po->isDraft())
                                    <a href="{{ route('purchasing.edit', $po) }}" class="p-1.5 inline-flex items-center text-amber-400 hover:text-amber-300 rounded-lg hover:bg-amber-500/10 transition" title="Edit Draft">
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
                                Belum ada data Purchase Order ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
