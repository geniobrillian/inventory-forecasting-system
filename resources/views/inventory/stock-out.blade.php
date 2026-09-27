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
                            <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Pengeluaran Barang</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-white tracking-tight">Pengeluaran Barang (Stock Out)</h1>
            <p class="text-sm text-slate-400 mt-0.5">Catat pengurangan stok barang untuk penjualan, pemakaian internal, atau barang rusak.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.overview') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 text-sm font-semibold hover:bg-slate-700 hover:border-slate-600 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali ke Ringkasan</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="stockOutForm()">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-rose-50/50 via-slate-50/30 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center font-bold">
                    <i class="ki-filled ki-minus-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">Formulir Mutasi Keluar</h3>
                    <p class="text-xs text-slate-500">Sistem akan memvalidasi kecukupan stok secara atomik sebelum pengeluaran diproses.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100/80 text-rose-700 border border-rose-200">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                Tipe: Stock Out
            </span>
        </div>

        <form action="{{ route('inventory.stock-out.process') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            <!-- Product & Warehouse Selection Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product -->
                <div>
                    <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Produk <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="product_id" id="product_id" x-model="productId" @change="fetchCurrentStock()" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('product_id') border-rose-500 ring-rose-500/10 @enderror">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $selectedProduct) == $product->id ? 'selected' : '' }}>
                                    [{{ $product->sku }}] {{ $product->name }} ({{ $product->unit->code ?? 'Unit' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('product_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warehouse -->
                <div>
                    <label for="warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Gudang Asal <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="warehouse_id" id="warehouse_id" x-model="warehouseId" @change="fetchCurrentStock()" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('warehouse_id') border-rose-500 ring-rose-500/10 @enderror">
                            <option value="">-- Pilih Gudang Asal --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id', $selectedWarehouse) == $wh->id ? 'selected' : '' }}>
                                    [{{ $wh->code }}] {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('warehouse_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Stock Live Checker Banner -->
            <div x-show="productId && warehouseId" x-transition class="p-4 rounded-xl border flex items-center justify-between"
                :class="isDeficit() ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200/80'">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold"
                        :class="isDeficit() ? 'bg-rose-100 text-rose-600' : 'bg-indigo-50 text-indigo-600'">
                        <i class="ki-filled" :class="isDeficit() ? 'ki-information-2' : 'ki-cube-2'"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium" :class="isDeficit() ? 'text-rose-600' : 'text-slate-500'">Stok Tersedia Saat Ini di Gudang Asal</p>
                        <p class="text-sm font-bold" :class="isDeficit() ? 'text-rose-700' : 'text-slate-900'" x-text="loadingStock ? 'Memuat...' : currentStock + ' ' + unitCode"></p>
                    </div>
                </div>
                <div class="text-right" x-show="!loadingStock && qty > 0">
                    <p class="text-xs font-medium" :class="isDeficit() ? 'text-rose-600' : 'text-slate-500'">Sisa Stok Setelah Keluar</p>
                    <p class="text-sm font-bold" :class="isDeficit() ? 'text-rose-700' : 'text-slate-900'" x-text="(parseFloat(currentStock) - parseFloat(qty || 0)) + ' ' + unitCode"></p>
                </div>
            </div>

            <!-- Deficit Warning Alert -->
            <div x-show="isDeficit()" x-transition class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                <i class="ki-filled ki-cross-circle text-rose-500 text-lg mt-0.5"></i>
                <div class="text-xs text-rose-700">
                    <span class="font-bold">Peringatan Defisit Stok:</span> Jumlah keluar melebihi stok yang tersedia saat ini. Transaksi akan ditolak oleh sistem untuk mencegah saldo minus.
                </div>
            </div>

            <!-- Quantity & Date Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jumlah Keluar <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="any" min="0.0001" name="quantity" id="quantity" x-model="qty" value="{{ old('quantity') }}" required placeholder="Contoh: 10"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 font-semibold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('quantity') border-rose-500 ring-rose-500/10 @enderror">
                    </div>
                    @error('quantity')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tanggal Transaksi <span class="text-rose-500">*</span>
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
                        No. Referensi / Invoice / DO
                    </label>
                    <input type="text" name="reference_id" id="reference_id" value="{{ old('reference_id') }}" placeholder="Contoh: INV-2026-09-001"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('reference_id') border-rose-500 ring-rose-500/10 @enderror">
                    <p class="mt-1 text-[11px] text-slate-400">Nomor faktur penjualan atau surat jalan pengiriman (opsional).</p>
                    @error('reference_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Keterangan / Alasan Pengeluaran
                    </label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Contoh: Penjualan ritel / pemakaian operasional..."
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
                <button type="submit" :disabled="isDeficit()" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold shadow-sm hover:shadow-rose-600/20 transition-all">
                    <i class="ki-filled ki-check text-base"></i>
                    <span>Proses Pengeluaran Stok</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function stockOutForm() {
    return {
        productId: '{{ old('product_id', $selectedProduct) }}',
        warehouseId: '{{ old('warehouse_id', $selectedWarehouse) }}',
        qty: '{{ old('quantity') }}',
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
                    this.loadingStock = false;
                })
                .catch(() => {
                    this.loadingStock = false;
                });
        },
        isDeficit() {
            if (!this.qty || !this.productId || !this.warehouseId) return false;
            return parseFloat(this.qty) > parseFloat(this.currentStock);
        }
    }
}
</script>
@endsection
