@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Breadcrumb & Header -->
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
                            <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Penyesuaian Stok (Opname)</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-white tracking-tight">Penyesuaian Stok Fisik (Stock Opname)</h1>
            <p class="text-sm text-slate-400 mt-0.5">Selaraskan kuantitas stok sistem dengan hasil penghitungan fisik gudang (Stock Opname / Adjustment).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.overview') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 text-sm font-semibold hover:bg-slate-700 hover:border-slate-600 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali ke Ringkasan</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="adjustmentForm()">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-amber-50/50 via-slate-50/30 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                    <i class="ki-filled ki-setting-4 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">Formulir Rekonsiliasi & Opname Stok</h3>
                    <p class="text-xs text-slate-500">Sistem akan menghitung selisih kuantitas dan mencatatnya sebagai transaksi penyesuaian.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-700 border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                Tipe: Stock Adjustment
            </span>
        </div>

        <form action="{{ route('inventory.adjustment.process') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            <!-- Product & Warehouse Selection Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product -->
                <div>
                    <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Produk <span class="text-rose-500">*</span>
                    </label>
                    <select name="product_id" id="product_id" x-model="productId" @change="fetchCurrentStock()" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('product_id') border-rose-500 ring-rose-500/10 @enderror">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id', $selectedProduct) == $product->id ? 'selected' : '' }}>
                                [{{ $product->sku }}] {{ $product->name }} ({{ $product->unit->code ?? 'Unit' }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warehouse -->
                <div>
                    <label for="warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Lokasi Gudang <span class="text-rose-500">*</span>
                    </label>
                    <select name="warehouse_id" id="warehouse_id" x-model="warehouseId" @change="fetchCurrentStock()" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('warehouse_id') border-rose-500 ring-rose-500/10 @enderror">
                        <option value="">-- Pilih Lokasi Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $selectedWarehouse) == $wh->id ? 'selected' : '' }}>
                                [{{ $wh->code }}] {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Comparison Metric Cards -->
            <div x-show="productId && warehouseId" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-xs text-slate-500 font-medium block mb-1">Stok Sistem Saat Ini</span>
                    <span class="text-lg font-bold text-slate-900" x-text="loadingStock ? 'Memuat...' : currentStock + ' ' + unitCode"></span>
                </div>
                <div class="p-4 rounded-xl bg-indigo-50/40 border border-indigo-100">
                    <span class="text-xs text-indigo-600 font-medium block mb-1">Stok Fisik Sebenarnya</span>
                    <span class="text-lg font-bold text-indigo-700" x-text="(actualQty !== '' ? actualQty : '0') + ' ' + unitCode"></span>
                </div>
                <div class="p-4 rounded-xl border"
                    :class="delta() > 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : (delta() < 0 ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-slate-50 border-slate-200 text-slate-700')">
                    <span class="text-xs font-medium block mb-1" :class="delta() > 0 ? 'text-emerald-600' : (delta() < 0 ? 'text-rose-600' : 'text-slate-500')">
                        Selisih (Delta Mutasi)
                    </span>
                    <div class="flex items-center gap-1.5 font-bold text-lg">
                        <i class="ki-filled" :class="delta() > 0 ? 'ki-arrow-up text-emerald-500' : (delta() < 0 ? 'ki-arrow-down text-rose-500' : 'ki-check text-slate-400')"></i>
                        <span x-text="(delta() > 0 ? '+' : '') + delta() + ' ' + unitCode"></span>
                    </div>
                </div>
            </div>

            <!-- Actual Quantity & Date Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Actual Quantity -->
                <div>
                    <label for="actual_quantity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kuantitas Fisik Aktual <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="any" min="0" name="actual_quantity" id="actual_quantity" x-model="actualQty" value="{{ old('actual_quantity') }}" required placeholder="Contoh: 100"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 font-bold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('actual_quantity') border-rose-500 ring-rose-500/10 @enderror">
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Masukkan total fisik akhir hasil opname di lapangan.</p>
                    @error('actual_quantity')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tanggal Opname <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('transaction_date') border-rose-500 ring-rose-500/10 @enderror">
                    </div>
                    @error('transaction_date')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Reference & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Reference Number -->
                <div>
                    <label for="reference_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        No. Berita Acara / Dokumen Opname
                    </label>
                    <input type="text" name="reference_id" id="reference_id" value="{{ old('reference_id') }}" placeholder="Contoh: BA-OPN-2026-09-001"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('reference_id') border-rose-500 ring-rose-500/10 @enderror">
                    <p class="mt-1 text-[11px] text-slate-400">Nomor BA stock opname atau dokumen verifikasi (opsional).</p>
                    @error('reference_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes / Reason -->
                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alasan / Keterangan Penyesuaian <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="notes" id="notes" rows="2" required placeholder="Contoh: Hasil stock opname bulanan / barang hilang / susut..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('notes') border-rose-500 ring-rose-500/10 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('inventory.overview') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-all">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold shadow-sm hover:shadow-amber-600/20 transition-all">
                    <i class="ki-filled ki-check text-base"></i>
                    <span>Terapkan Penyesuaian Stok</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function adjustmentForm() {
    return {
        productId: '{{ old('product_id', $selectedProduct) }}',
        warehouseId: '{{ old('warehouse_id', $selectedWarehouse) }}',
        actualQty: '{{ old('actual_quantity') }}',
        currentStock: 0,
        unitCode: 'Unit',
        loadingStock: false,
        init() {
            if (this.productId && this.warehouseId) {
                this.fetchCurrentStock();
            }
        },
        fetchCurrentStock() {
            if (!this.productId || !this.warehouseId) return;
            this.loadingStock = true;
            fetch(`{{ route('inventory.api.stock') }}?product_id=${this.productId}&warehouse_id=${this.warehouseId}`)
                .then(res => res.json())
                .then(data => {
                    this.currentStock = data.quantity;
                    this.unitCode = data.unit || 'Unit';
                    if (this.actualQty === '') {
                        this.actualQty = data.quantity;
                    }
                    this.loadingStock = false;
                })
                .catch(() => {
                    this.loadingStock = false;
                });
        },
        delta() {
            if (this.actualQty === '' || isNaN(this.actualQty)) return 0;
            return parseFloat(this.actualQty) - parseFloat(this.currentStock || 0);
        }
    }
}
</script>
@endsection
