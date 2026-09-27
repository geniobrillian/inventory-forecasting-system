@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $sale->invoice_number }}</h1>
                    @php
                        $statusClasses = match($sale->status) {
                            'DRAFT' => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                            'COMPLETED' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                            'CANCELLED' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                            default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }}">
                        {{ $sale->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">Kasir / Petugas: {{ $sale->creator?->name ?? 'System' }} • {{ $sale->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if($sale->isDraft())
                <a href="{{ route('sales.edit', $sale) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Draf</span>
                </a>

                <form action="{{ route('sales.complete', $sale) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan transaksi ini dan memotong stok gudang?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl border border-emerald-500/40 shadow-lg shadow-emerald-600/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Selesaikan & Potong Stok</span>
                    </button>
                </form>
            @endif

            @if($sale->canCancel())
                <form action="{{ route('sales.cancel', $sale) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi penjualan ini? {{ $sale->isCompleted() ? 'Stok akan dikembalikan ke gudang otomatis.' : '' }}')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 text-xs font-semibold rounded-xl border border-rose-500/30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batalkan Penjualan</span>
                    </button>
                </form>
            @endif

            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Faktur</span>
            </button>
        </div>
    </div>

    <!-- Invoice Header Box -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Customer Info -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Pelanggan</span>
            <div class="font-bold text-white text-sm">{{ $sale->customer_name ?: 'Pelanggan Umum (Walk-in)' }}</div>
            <div class="text-xs text-slate-400 mt-1">Telp: {{ $sale->customer_phone ?: '-' }}</div>
        </div>

        <!-- Origin Warehouse -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Gudang Pengeluaran</span>
            <div class="font-bold text-white text-sm">{{ $sale->warehouse?->name ?? '-' }}</div>
            <div class="text-xs text-slate-400 mt-1">Kode: {{ $sale->warehouse?->code ?? '-' }}</div>
        </div>

        <!-- Payment Info -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Informasi Pembayaran</span>
            <div class="text-xs text-slate-300">Metode: <span class="font-bold text-white">{{ $sale->payment_method }}</span></div>
            <div class="text-xs text-slate-300 mt-1">
                Status:
                @php
                    $payClasses = match($sale->payment_status) {
                        'PAID' => 'text-emerald-400',
                        'PARTIAL' => 'text-amber-400',
                        'UNPAID' => 'text-rose-400',
                        default => 'text-slate-400',
                    };
                @endphp
                <span class="font-bold {{ $payClasses }}">{{ $sale->payment_status }}</span>
            </div>
        </div>

        <!-- Date & Cancellation info if any -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Tanggal Transaksi</span>
            <div class="font-bold text-white text-sm">{{ $sale->sale_date?->format('d M Y') ?? '-' }}</div>
            @if($sale->cancelled_at)
                <div class="text-[11px] text-rose-400 mt-1">Dibatalkan: {{ $sale->cancelled_at->format('d M Y H:i') }}</div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-4">
        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span>Daftar Barang yang Dijual</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase">
                    <tr>
                        <th class="px-4 py-3">Produk / SKU</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Harga Jual Satuan</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Pajak</th>
                        <th class="px-4 py-3 text-right">Total Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($sale->items as $item)
                        <tr class="hover:bg-slate-800/20">
                            <td class="px-4 py-3">
                                <div class="font-bold text-white">{{ $item->product?->name ?? 'Produk Dihapus' }}</div>
                                <div class="text-[11px] text-slate-500">SKU: {{ $item->product?->sku ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-white">
                                {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->product?->unit?->symbol }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-300">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-rose-400">
                                {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-emerald-400">
                                {{ $item->tax_percent > 0 ? $item->tax_percent . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-100">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-800">
            <!-- Notes -->
            <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800/80">
                <span class="text-xs font-semibold text-slate-400 block mb-1">Catatan Transaksi:</span>
                <p class="text-xs text-slate-300 italic">{{ $sale->notes ?: 'Tidak ada catatan tambahan.' }}</p>
                @if($sale->cancellation_reason)
                    <div class="mt-2 text-xs text-rose-400">
                        <span class="font-bold">Alasan Pembatalan:</span> {{ $sale->cancellation_reason }}
                    </div>
                @endif
            </div>

            <!-- Cost Totals -->
            <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800/80 space-y-2 text-xs">
                <div class="flex justify-between text-slate-300">
                    <span>Subtotal Barang:</span>
                    <span class="font-semibold text-white">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Total Diskon:</span>
                    <span class="font-semibold text-rose-400">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Total Pajak:</span>
                    <span class="font-semibold text-emerald-400">+ Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Biaya Pengiriman:</span>
                    <span class="font-semibold text-white">Rp {{ number_format($sale->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-800 text-sm font-bold text-white">
                    <span>Total Tagihan:</span>
                    <span class="text-emerald-400 text-base">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300 pt-1">
                    <span>Uang Diterima:</span>
                    <span class="font-semibold text-white">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Kembalian:</span>
                    <span class="font-semibold text-indigo-400">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
