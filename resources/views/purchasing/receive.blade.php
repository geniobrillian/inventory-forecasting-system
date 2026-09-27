@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.show', $purchase) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-white tracking-tight">Penerimaan Barang (GRN): {{ $purchase->purchase_number }}</h1>
                <p class="text-xs text-slate-400">Pencatatan fisik barang masuk dan mutasi stok otomatis ke gudang</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
            <div class="font-bold">Terdapat kesalahan pengisian penerimaan:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- PO Summary Card -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div>
            <span class="text-slate-400 block mb-1">Supplier:</span>
            <span class="font-bold text-white text-sm">{{ $purchase->supplier?->name }}</span>
        </div>
        <div>
            <span class="text-slate-400 block mb-1">Gudang Penerima:</span>
            <span class="font-bold text-white text-sm">{{ $purchase->warehouse?->name }} ({{ $purchase->warehouse?->code }})</span>
        </div>
        <div>
            <span class="text-slate-400 block mb-1">Status Saat Ini:</span>
            <span class="font-bold text-sky-400">{{ $purchase->status }} ({{ $purchase->receiving_progress_percent }}% Diterima)</span>
        </div>
    </div>

    <!-- Goods Receiving Form -->
    <form action="{{ route('purchasing.receive.process', $purchase) }}" method="POST">
        @csrf

        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Penerimaan Fisik <span class="text-rose-400">*</span></label>
                    <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required
                           class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Surat Jalan / Catatan Penerimaan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Surat Jalan SJ-2026-889..."
                           class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <!-- Items Table to Receive -->
            <div class="space-y-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Jumlah Barang yang Diterima Saat Ini</span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-right">Total Dipesan</th>
                                <th class="px-4 py-3 text-right">Sudah Diterima</th>
                                <th class="px-4 py-3 text-right">Sisa Belum Diterima</th>
                                <th class="px-4 py-3 text-right w-48">Jumlah Diterima Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($purchase->items as $index => $item)
                                <tr class="hover:bg-slate-800/20">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->id }}">
                                        <div class="font-bold text-white">{{ $item->product?->name }}</div>
                                        <div class="text-[11px] text-slate-500">SKU: {{ $item->product?->sku }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-slate-300">
                                        {{ number_format($item->quantity, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-400">
                                        {{ number_format($item->received_quantity, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-400">
                                        {{ number_format($item->remaining_quantity, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($item->remaining_quantity > 0)
                                            <input type="number" name="items[{{ $index }}][received_quantity]"
                                                   value="{{ old("items.{$index}.received_quantity", $item->remaining_quantity) }}"
                                                   min="0" max="{{ $item->remaining_quantity }}" step="1" required
                                                   class="w-32 bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-right font-bold text-emerald-400 focus:outline-none focus:border-emerald-500">
                                        @else
                                            <span class="text-xs text-emerald-400 font-semibold italic">Sudah Lengkap</span>
                                            <input type="hidden" name="items[{{ $index }}][received_quantity]" value="0">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('purchasing.show', $purchase) }}"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl border border-slate-700 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 border border-emerald-500/50 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Konfirmasi Penerimaan Barang Masuk</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
