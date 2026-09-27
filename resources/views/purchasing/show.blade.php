@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $purchase->purchase_number }}</h1>
                    @php
                        $statusClasses = match($purchase->status) {
                            'DRAFT' => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                            'ORDERED' => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
                            'PARTIALLY_RECEIVED' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                            'RECEIVED' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                            'CANCELLED' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                            default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }}">
                        {{ $purchase->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">Dibuat oleh {{ $purchase->creator?->name ?? 'System' }} pada {{ $purchase->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <!-- Actions Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if($purchase->isDraft())
                <a href="{{ route('purchasing.edit', $purchase) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit PO</span>
                </a>

                <form action="{{ route('purchasing.order', $purchase) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses PO ini ke status ORDERED?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl border border-sky-500/40 shadow-lg shadow-sky-600/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Pesan Sekarang (Order)</span>
                    </button>
                </form>
            @endif

            @if($purchase->canReceive())
                <a href="{{ route('purchasing.receive', $purchase) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl border border-emerald-500/40 shadow-lg shadow-emerald-600/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Terima Barang (GRN)</span>
                </a>
            @endif

            @if($purchase->canCancel())
                <form action="{{ route('purchasing.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 text-xs font-semibold rounded-xl border border-rose-500/30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batalkan PO</span>
                    </button>
                </form>
            @endif

            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Main Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Supplier Card -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Supplier</span>
            <div class="font-bold text-white text-sm">{{ $purchase->supplier?->name ?? '-' }}</div>
            <div class="text-xs text-slate-400 mt-1">Kontak: {{ $purchase->supplier?->contact_person ?? '-' }} ({{ $purchase->supplier?->phone ?? '-' }})</div>
            <div class="text-[11px] text-slate-500 truncate">{{ $purchase->supplier?->address ?? '-' }}</div>
        </div>

        <!-- Warehouse Destination -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Gudang Tujuan</span>
            <div class="font-bold text-white text-sm">{{ $purchase->warehouse?->name ?? '-' }}</div>
            <div class="text-xs text-slate-400 mt-1">Kode: {{ $purchase->warehouse?->code ?? '-' }}</div>
            <div class="text-[11px] text-slate-500 truncate">{{ $purchase->warehouse?->address ?? '-' }}</div>
        </div>

        <!-- Dates Card -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Jadwal Pengiriman</span>
            <div class="text-xs text-slate-300">Tgl Order: <span class="font-bold text-white">{{ $purchase->order_date?->format('d M Y') ?? '-' }}</span></div>
            <div class="text-xs text-slate-300 mt-1">Estimasi Tiba: <span class="font-bold text-white">{{ $purchase->expected_delivery_date?->format('d M Y') ?? 'Belum ditentukan' }}</span></div>
        </div>

        <!-- Receiving Progress Card -->
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Progres Penerimaan</span>
            <div class="flex items-center justify-between text-xs font-bold text-white mb-1.5">
                <span>Total Fisik Diterima</span>
                <span class="text-indigo-400">{{ $purchase->receiving_progress_percent }}%</span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden border border-slate-700/50">
                <div class="bg-indigo-500 h-2.5 rounded-full transition-all duration-300" style="width: {{ $purchase->receiving_progress_percent }}%"></div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-4">
        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>Rincian Barang Dipesan</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase">
                    <tr>
                        <th class="px-4 py-3">Produk / SKU</th>
                        <th class="px-4 py-3 text-right">Qty Dipesan</th>
                        <th class="px-4 py-3 text-right">Qty Diterima</th>
                        <th class="px-4 py-3 text-right">Sisa Qty</th>
                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Pajak</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-center">Status Item</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($purchase->items as $item)
                        <tr class="hover:bg-slate-800/20">
                            <td class="px-4 py-3">
                                <div class="font-bold text-white">{{ $item->product?->name ?? 'Produk Dihapus' }}</div>
                                <div class="text-[11px] text-slate-500">SKU: {{ $item->product?->sku ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-200">
                                {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->product?->unit?->symbol }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-400">
                                {{ number_format($item->received_quantity, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $item->remaining_quantity > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                                {{ number_format($item->remaining_quantity, 0, ',', '.') }}
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
                            <td class="px-4 py-3 text-center">
                                @if($item->isFullyReceived())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        Lengkap
                                    </span>
                                @elseif($item->received_quantity > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                        Sebagian
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-700/40 text-slate-400 border border-slate-600/50">
                                        Belum Ada
                                    </span>
                                @endif
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
                <span class="text-xs font-semibold text-slate-400 block mb-1">Catatan PO:</span>
                <p class="text-xs text-slate-300 italic">{{ $purchase->notes ?: 'Tidak ada catatan tambahan.' }}</p>
            </div>

            <!-- Cost Totals -->
            <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800/80 space-y-2 text-xs">
                <div class="flex justify-between text-slate-300">
                    <span>Subtotal Barang:</span>
                    <span class="font-semibold text-white">Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Total Diskon:</span>
                    <span class="font-semibold text-rose-400">- Rp {{ number_format($purchase->discount_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Total Pajak:</span>
                    <span class="font-semibold text-emerald-400">+ Rp {{ number_format($purchase->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Ongkos Kirim:</span>
                    <span class="font-semibold text-white">Rp {{ number_format($purchase->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-800 text-sm font-bold text-white">
                    <span>Grand Total:</span>
                    <span class="text-indigo-400 text-base">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
