@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="purchaseForm()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.show', $purchase) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-white tracking-tight">Edit Purchase Order: {{ $purchase->purchase_number }}</h1>
                <p class="text-xs text-slate-400">Modifikasi data draf pemesanan barang</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
            <div class="font-bold">Terdapat kesalahan pengisian formulir:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchasing.update', $purchase) }}" method="POST" @submit="handleSubmit($event)">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Info & Items Table -->
            <div class="lg:col-span-2 space-y-6">
                <!-- PO Meta Details Card -->
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Informasi Purchase Order</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor PO</label>
                            <input type="text" name="purchase_number" value="{{ $purchase->purchase_number }}" readonly
                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 text-xs text-indigo-300 font-mono font-bold cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                            <select name="status" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="DRAFT" {{ old('status', $purchase->status) === 'DRAFT' ? 'selected' : '' }}>DRAFT (Simpan Sebagai Draf)</option>
                                <option value="ORDERED" {{ old('status', $purchase->status) === 'ORDERED' ? 'selected' : '' }}>ORDERED (Langsung Pesan ke Supplier)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Supplier <span class="text-rose-400">*</span></label>
                            <select name="supplier_id" required class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} ({{ $supplier->contact_person ?? 'No Contact' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Gudang Tujuan <span class="text-rose-400">*</span></label>
                            <select name="warehouse_id" required class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Pilih Gudang Penerima --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $purchase->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Pemesanan <span class="text-rose-400">*</span></label>
                            <input type="date" name="order_date" value="{{ old('order_date', $purchase->order_date?->format('Y-m-d')) }}" required
                                   class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Estimasi Tanggal Pengiriman</label>
                            <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', $purchase->expected_delivery_date?->format('Y-m-d')) }}"
                                   class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Line Items Card -->
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>Daftar Barang (Line Items)</span>
                        </h2>
                        <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-xs font-semibold rounded-xl border border-indigo-500/30 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah Baris</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase">
                                <tr>
                                    <th class="px-3 py-2.5 min-w-[200px]">Produk</th>
                                    <th class="px-3 py-2.5 w-24">Jumlah</th>
                                    <th class="px-3 py-2.5 w-36">Harga Satuan (Rp)</th>
                                    <th class="px-3 py-2.5 w-24">Diskon (%)</th>
                                    <th class="px-3 py-2.5 w-24">Pajak (%)</th>
                                    <th class="px-3 py-2.5 w-36 text-right">Subtotal</th>
                                    <th class="px-3 py-2.5 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-800/20">
                                        <!-- Product Selection -->
                                        <td class="px-3 py-2">
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductChange(item)" required
                                                    class="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                                <option value="">-- Pilih Produk --</option>
                                                <template x-for="prod in products" :key="prod.id">
                                                    <option :value="prod.id" x-text="`${prod.name} (${prod.sku})`" :selected="prod.id == item.product_id"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <!-- Quantity -->
                                        <td class="px-3 py-2">
                                            <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required
                                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200 text-center focus:outline-none focus:border-indigo-500">
                                        </td>
                                        <!-- Unit Price -->
                                        <td class="px-3 py-2">
                                            <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" min="0" step="100" required
                                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200 text-right focus:outline-none focus:border-indigo-500">
                                        </td>
                                        <!-- Discount Rate -->
                                        <td class="px-3 py-2">
                                            <input type="number" :name="`items[${index}][discount_percent]`" x-model.number="item.discount_percent" min="0" max="100" step="0.1"
                                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200 text-center focus:outline-none focus:border-indigo-500">
                                        </td>
                                        <!-- Tax Rate -->
                                        <td class="px-3 py-2">
                                            <input type="number" :name="`items[${index}][tax_percent]`" x-model.number="item.tax_percent" min="0" max="100" step="0.1"
                                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200 text-center focus:outline-none focus:border-indigo-500">
                                        </td>
                                        <!-- Line Total -->
                                        <td class="px-3 py-2 text-right font-semibold text-slate-200">
                                            <span x-text="formatRupiah(calculateLineTotal(item))"></span>
                                        </td>
                                        <!-- Delete Action -->
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                                    class="p-1 text-slate-500 hover:text-rose-400 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Col: Summary & Additional Information -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Ringkasan Biaya</span>
                    </h2>

                    <div class="space-y-2.5 text-xs text-slate-300">
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span>Subtotal Produk</span>
                            <span class="font-semibold text-slate-100" x-text="formatRupiah(subtotal())"></span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span>Total Diskon</span>
                            <span class="font-semibold text-rose-400" x-text="'- ' + formatRupiah(totalDiscount())"></span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span>Total Pajak</span>
                            <span class="font-semibold text-emerald-400" x-text="'+ ' + formatRupiah(totalTax())"></span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-slate-800">
                            <label class="text-slate-300">Ongkos Kirim (Rp)</label>
                            <input type="number" name="shipping_cost" x-model.number="shippingCost" min="0" step="1000"
                                   class="w-32 bg-slate-950/80 border border-slate-800 rounded-lg px-2 py-1 text-xs text-right text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div class="flex justify-between pt-3 text-sm font-bold text-white">
                            <span>Grand Total</span>
                            <span class="text-indigo-400 text-base" x-text="formatRupiah(grandTotal())"></span>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm space-y-3">
                    <label class="block text-xs font-semibold text-slate-300">Catatan Pemesanan / Instruksi Khusus</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Pengiriman via ekspedisi khusus..."
                              class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">{{ old('notes', $purchase->notes) }}</textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="space-y-2">
                    <button type="submit"
                            class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 border border-indigo-400/30 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Perbarui Purchase Order</span>
                    </button>

                    <a href="{{ route('purchasing.show', $purchase) }}"
                       class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 font-semibold text-xs rounded-xl border border-slate-800 transition flex items-center justify-center">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function purchaseForm() {
        return {
            products: @json($products),
            shippingCost: {{ old('shipping_cost', $purchase->shipping_cost ?? 0) }},
            items: @json($purchase->items->map(function($i) {
                return [
                    'product_id' => $i->product_id,
                    'quantity' => $i->quantity,
                    'unit_price' => (float)$i->unit_price,
                    'discount_percent' => (float)$i->discount_percent,
                    'tax_percent' => (float)$i->tax_percent,
                ];
            })),
            addItem() {
                this.items.push({
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    discount_percent: 0,
                    tax_percent: 0
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            onProductChange(item) {
                const prod = this.products.find(p => p.id == item.product_id);
                if (prod) {
                    item.unit_price = parseFloat(prod.purchase_price) || 0;
                }
            },
            calculateLineTotal(item) {
                const qty = parseFloat(item.quantity) || 0;
                const price = parseFloat(item.unit_price) || 0;
                const discRate = parseFloat(item.discount_percent) || 0;
                const taxRate = parseFloat(item.tax_percent) || 0;

                const base = qty * price;
                const discount = base * (discRate / 100);
                const afterDisc = base - discount;
                const tax = afterDisc * (taxRate / 100);

                return Math.max(0, afterDisc + tax);
            },
            subtotal() {
                return this.items.reduce((acc, item) => {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    return acc + (qty * price);
                }, 0);
            },
            totalDiscount() {
                return this.items.reduce((acc, item) => {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const discRate = parseFloat(item.discount_percent) || 0;
                    return acc + ((qty * price) * (discRate / 100));
                }, 0);
            },
            totalTax() {
                return this.items.reduce((acc, item) => {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const discRate = parseFloat(item.discount_percent) || 0;
                    const taxRate = parseFloat(item.tax_percent) || 0;
                    const afterDisc = (qty * price) - ((qty * price) * (discRate / 100));
                    return acc + (afterDisc * (taxRate / 100));
                }, 0);
            },
            grandTotal() {
                const total = this.subtotal() - this.totalDiscount() + this.totalTax() + (parseFloat(this.shippingCost) || 0);
                return Math.max(0, total);
            },
            formatRupiah(val) {
                return 'Rp ' + Math.round(val).toLocaleString('id-ID');
            },
            handleSubmit(e) {
                if (this.items.some(i => !i.product_id || i.quantity <= 0)) {
                    e.preventDefault();
                    alert('Harap pastikan semua baris produk telah dipilih dan jumlah lebih dari 0.');
                }
            }
        };
    }
</script>
@endsection
