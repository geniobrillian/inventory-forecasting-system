@extends('layouts.app', ['title' => 'Detail Supplier ' . $supplier->name])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('suppliers.index') }}" class="hover:text-slate-200 transition">Pemasok</a>
                <span>/</span>
                <span class="text-indigo-400 font-medium">{{ $supplier->code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $supplier->name }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $supplier->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                    {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <p class="text-sm font-mono text-indigo-400 mt-0.5">Kode: {{ $supplier->code }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.edit', $supplier) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Supplier</span>
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                <span>Daftar Supplier</span>
            </a>
        </div>
    </div>

    <!-- Supplier Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Profile & Contact Card -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profil & Kontak
            </h3>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Kontak Person (PIC)</span>
                    <span class="font-semibold text-slate-200">{{ $supplier->contact_person ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Nomor Telepon</span>
                    <span class="font-semibold text-slate-200">{{ $supplier->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Email Perusahaan</span>
                    <span class="font-semibold text-slate-200">{{ $supplier->email ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Alamat</span>
                    <p class="text-slate-300 text-xs mt-1 leading-relaxed">{{ $supplier->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Procurement Stats -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Parameter Pengadaan
            </h3>

            <div class="space-y-4">
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400 block">Default Lead Time Pengiriman</span>
                    <div class="text-2xl font-bold text-amber-400 mt-0.5 flex items-baseline gap-1">
                        {{ $supplier->default_lead_time_days }}
                        <span class="text-xs text-slate-400 font-normal">Hari Kalender</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Digunakan oleh algoritma kalkulasi Reorder Point (ROP).</p>
                </div>

                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400 block">Total Item yang Disuplai</span>
                    <div class="text-2xl font-bold text-indigo-400 mt-0.5 flex items-baseline gap-1">
                        {{ $productsCount }}
                        <span class="text-xs text-slate-400 font-normal">Produk SKU</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit & System Info -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Audit Sistem
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block">ID Sistem</span>
                    <span class="font-mono text-slate-300">#{{ $supplier->id }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block">Terdaftar Pada</span>
                    <span class="text-slate-300">{{ $supplier->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block">Terakhir Diperbarui</span>
                    <span class="text-slate-300">{{ $supplier->updated_at?->translatedFormat('d F Y, H:i') ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplied Products Table -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-sm">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Produk yang Disuplai</h3>
            <span class="text-xs text-slate-400 font-medium">Menampilkan {{ $supplier->products->count() }} dari {{ $productsCount }} produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="px-6 py-3.5">SKU & Nama Produk</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Harga Beli</th>
                        <th class="px-6 py-3.5">Min. Stok</th>
                        <th class="px-6 py-3.5">Metode Forecast</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($supplier->products as $prod)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $prod->name }}</div>
                                <div class="text-xs font-mono text-indigo-400">{{ $prod->sku }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-slate-800 text-slate-300 rounded-lg text-xs">
                                    {{ $prod->category?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-emerald-400">
                                Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ number_format($prod->minimum_stock) }} {{ $prod->unit?->code ?? '' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold {{ $prod->forecast_method === 'MOVING_AVERAGE' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border border-purple-500/20' }}">
                                    {{ $prod->forecast_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('products.show', $prod) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                                    Lihat Produk &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 text-xs">
                                Belum ada produk yang terhubung dengan supplier ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
