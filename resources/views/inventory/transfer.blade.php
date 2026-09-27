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
                            <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Transfer Antar Gudang</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-white tracking-tight">Transfer Stok Antar-Gudang</h1>
            <p class="text-sm text-slate-400 mt-0.5">Pindahkan stok produk antar lokasi gudang secara atomik dan terintegrasi dalam satu transaksi.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.overview') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 text-sm font-semibold hover:bg-slate-700 hover:border-slate-600 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali ke Ringkasan</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="transferForm()">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-blue-50/50 via-slate-50/30 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center font-bold">
                    <i class="ki-filled ki-arrows-loop text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">Formulir Relokasi & Transfer Stok</h3>
                    <p class="text-xs text-slate-500">Mutasi keluar pada gudang asal dan mutasi masuk pada gudang tujuan dieksekusi secara serentak.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100/80 text-blue-700 border border-blue-200">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                Tipe: Atomic Transfer
            </span>
        </div>

        <form action="{{ route('inventory.transfer.process') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            <!-- Product Selection -->
            <div>
                <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Pilih Produk Yang Ditransfer <span class="text-rose-500">*</span>
                </label>
                <select name="product_id" id="product_id" x-model="productId" @change="fetchSourceStock()" required
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

            <!-- Warehouses Selection Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 rounded-2xl bg-slate-50/60 border border-slate-200/60">
                <!-- From Warehouse -->
                <div>
                    <label for="from_warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Gudang Asal (Sumber) <span class="text-rose-500">*</span>
                    </label>
                    <select name="from_warehouse_id" id="from_warehouse_id" x-model="fromWarehouseId" @change="fetchSourceStock()" required
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('from_warehouse_id') border-rose-500 ring-rose-500/10 @enderror">
                        <option value="">-- Pilih Gudang Asal --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id', $selectedWarehouse) == $wh->id ? 'selected' : '' }}>
                                [{{ $wh->code }}] {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- To Warehouse -->
                <div>
                    <label for="to_warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Gudang Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <select name="to_warehouse_id" id="to_warehouse_id" x-model="toWarehouseId" required
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('to_warehouse_id') border-rose-500 ring-rose-500/10 @enderror">
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }} :disabled="fromWarehouseId == {{ $wh->id }}">
                                [{{ $wh->code }}] {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Source Stock Live Checker Banner -->
            <div x-show="productId && fromWarehouseId" x-transition class="p-4 rounded-xl border flex items-center justify-between"
                :class="isDeficit() ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200/80'">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold"
                        :class="isDeficit() ? 'bg-rose-100 text-rose-600' : 'bg-blue-50 text-blue-600'">
                        <i class="ki-filled" :class="isDeficit() ? 'ki-information-2' : 'ki-cube-2'"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium" :class="isDeficit() ? 'text-rose-600' : 'text-slate-500'">Stok Tersedia di Gudang Asal</p>
                        <p class="text-sm font-bold" :class="isDeficit() ? 'text-rose-700' : 'text-slate-900'" x-text="loadingStock ? 'Memuat...' : sourceStock + ' ' + unitCode"></p>
                    </div>
                </div>
                <div class="text-right" x-show="!loadingStock && qty > 0">
                    <p class="text-xs font-medium" :class="isDeficit() ? 'text-rose-600' : 'text-slate-500'">Sisa di Gudang Asal</p>
                    <p class="text-sm font-bold" :class="isDeficit() ? 'text-rose-700' : 'text-slate-900'" x-text="(parseFloat(sourceStock) - parseFloat(qty || 0)) + ' ' + unitCode"></p>
                </div>
            </div>

            <!-- Deficit Warning Alert -->
            <div x-show="isDeficit()" x-transition class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                <i class="ki-filled ki-cross-circle text-rose-500 text-lg mt-0.5"></i>
                <div class="text-xs text-rose-700">
                    <span class="font-bold">Peringatan:</span> Jumlah transfer melebihi stok yang tersedia di gudang asal.
                </div>
            </div>

            <!-- Same Warehouse Alert -->
            <div x-show="fromWarehouseId && toWarehouseId && fromWarehouseId === toWarehouseId" x-transition class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                <i class="ki-filled ki-information-2 text-amber-500 text-lg mt-0.5"></i>
                <div class="text-xs text-amber-800">
                    <span class="font-bold">Peringatan:</span> Gudang asal dan gudang tujuan tidak boleh sama.
                </div>
            </div>

            <!-- Quantity & Date Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jumlah Transfer <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="any" min="0.0001" name="quantity" id="quantity" x-model="qty" value="{{ old('quantity') }}" required placeholder="Contoh: 25"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 font-semibold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('quantity') border-rose-500 ring-rose-500/10 @enderror">
                    </div>
                    @error('quantity')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tanggal Transfer <span class="text-rose-500">*</span>
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
                        No. Surat Jalan / Memo Transfer
                    </label>
                    <input type="text" name="reference_id" id="reference_id" value="{{ old('reference_id') }}" placeholder="Contoh: TRF-2026-09-001"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none @error('reference_id') border-rose-500 ring-rose-500/10 @enderror">
                    <p class="mt-1 text-[11px] text-slate-400">Nomor dokumen surat jalan relokasi (opsional).</p>
                    @error('reference_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Keterangan Transfer
                    </label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Alasan pemindahan / catatan armada..."
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
                <button type="submit" :disabled="isDeficit() || fromWarehouseId === toWarehouseId" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold shadow-sm hover:shadow-blue-600/20 transition-all">
                    <i class="ki-filled ki-arrows-loop text-base"></i>
                    <span>Eksekusi Transfer Stok</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function transferForm() {
    return {
        productId: '{{ old('product_id', $selectedProduct) }}',
        fromWarehouseId: '{{ old('from_warehouse_id', $selectedWarehouse) }}',
        toWarehouseId: '{{ old('to_warehouse_id') }}',
        qty: '{{ old('quantity') }}',
        sourceStock: 0,
        unitCode: 'Unit',
        loadingStock: false,
        init() {
            if (this.productId && this.fromWarehouseId) {
                this.fetchSourceStock();
            }
        },
        fetchSourceStock() {
            if (!this.productId || !this.fromWarehouseId) return;
            this.loadingStock = true;
            fetch(`{{ route('inventory.api.stock') }}?product_id=${this.productId}&warehouse_id=${this.fromWarehouseId}`)
                .then(res => res.json())
                .then(data => {
                    this.sourceStock = data.quantity;
                    this.unitCode = data.unit || 'Unit';
                    this.loadingStock = false;
                })
                .catch(() => {
                    this.loadingStock = false;
                });
        },
        isDeficit() {
            if (!this.qty || !this.productId || !this.fromWarehouseId) return false;
            return parseFloat(this.qty) > parseFloat(this.sourceStock);
        }
    }
}
</script>
@endsection
