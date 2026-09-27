<x-app-layout>
    <x-slot name="header">
        Analytics Dashboard
    </x-slot>

    <div class="space-y-8 pb-10" x-data="dashboardAnalytics()">
        <!-- Welcome & Quick Action Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950/90 via-slate-900 to-slate-900 border border-indigo-500/20 p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/15 border border-emerald-500/30 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Analytics Engine
                        </span>
                        <span class="text-xs text-slate-400">• Update Real-time</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Selamat Datang, {{ $user->name }} 👋
                    </h2>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed">
                        Pantau kesehatan persediaan multi-gudang, valuasi aset, laju perputaran barang (*turnover*), dan tren permintaan riil secara komprehensif.
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2.5">
                    @if(auth()->user()->hasAnyRole(['super_admin', 'warehouse_staff']))
                        <a href="{{ route('inventory.stock-in') }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 text-xs font-semibold rounded-xl border border-emerald-500/30 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Stock In</span>
                        </a>

                        <a href="{{ route('inventory.stock-out') }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 text-xs font-semibold rounded-xl border border-rose-500/30 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            <span>Stock Out</span>
                        </a>
                    @endif

                    @if(auth()->user()->hasAnyRole(['super_admin', 'purchasing']))
                        <a href="{{ route('purchasing.create') }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-sky-600/20 hover:bg-sky-600/30 text-sky-300 text-xs font-semibold rounded-xl border border-sky-500/30 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span>Buat PO</span>
                        </a>
                    @endif

                    @if(auth()->user()->hasAnyRole(['super_admin', 'warehouse_staff', 'manager']))
                        <a href="{{ route('sales.create') }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl border border-indigo-500/50 shadow-lg shadow-indigo-600/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Transaksi Kasir</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Background Decorative Glow -->
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -top-20 w-80 h-80 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Primary KPI Metrics (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1: Total Products -->
            <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-sm relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total SKU Produk</p>
                        <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($kpi['total_products'], 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-500 mt-1">Katalog produk aktif</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Stock Units -->
            <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-sm relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Kuantitas Fisik</p>
                        <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($kpi['total_stock_quantity'], 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-500 mt-1">Unit barang di semua gudang</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-violet-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Inventory Valuation -->
            <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-sm relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Valuasi Stok</p>
                        <h3 class="text-2xl font-extrabold text-emerald-400 mt-1">Rp {{ number_format($kpi['total_inventory_value'], 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-500 mt-1">Berdasarkan harga pokok beli (COGS)</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 4: 30-Day Sales & Turnover Ratio -->
            <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-sm relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penjualan 30 Hari</p>
                        <h3 class="text-2xl font-extrabold text-sky-400 mt-1">Rp {{ number_format($kpi['total_sales_value_30d'], 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-400 mt-1">Turnover Ratio: <span class="text-white font-bold">{{ $kpi['turnover_ratio'] }}x</span> / tahun</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Health Alert Strip (4 Status Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Healthy -->
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider block">Stok Aman (Healthy)</span>
                        <span class="text-xl font-black text-white">{{ $health['healthy'] }} SKU</span>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/25 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider block">Menipis (Low Stock)</span>
                        <span class="text-xl font-black text-white">{{ $health['low_stock'] }} SKU</span>
                    </div>
                </div>
            </div>

            <!-- Critical Stock -->
            <div class="p-4 rounded-2xl bg-orange-500/10 border border-orange-500/25 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-orange-400 uppercase tracking-wider block">Kritis (Critical)</span>
                        <span class="text-xl font-black text-white">{{ $health['critical'] }} SKU</span>
                    </div>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-rose-400 uppercase tracking-wider block">Habis (Out of Stock)</span>
                        <span class="text-xl font-black text-white">{{ $health['out_of_stock'] }} SKU</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Charts Grid (2 Cols Top + 2 Cols Bottom) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart 1: Demand Trend -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                            <span>Tren Permintaan Penjualan (14 Hari Terakhir)</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Volume barang keluar harian dari transaksi penjualan riil</p>
                    </div>
                </div>
                <div id="demandTrendChart" class="w-full h-72"></div>
            </div>

            <!-- Chart 2: Stock In vs Stock Out Comparison -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span>Arus Mutasi Barang (6 Bulan Terakhir)</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Perbandingan total barang masuk vs barang keluar bulanan</p>
                    </div>
                </div>
                <div id="stockMovementChart" class="w-full h-72"></div>
            </div>
        </div>

        <!-- Secondary Charts & Velocity Analysis Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart 3: Warehouse Distribution (1 Col) -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-xl backdrop-blur-sm space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Distribusi Stok Gudang</span>
                </h3>
                <div id="warehouseDistributionChart" class="w-full h-64 flex items-center justify-center"></div>
            </div>

            <!-- Fast-Moving Products (1 Col) -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Top Produk Terlaris (Fast-Moving)</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($topSelling as $item)
                        <div class="p-3 rounded-xl bg-slate-950/50 border border-slate-800 flex items-center justify-between hover:border-slate-700 transition">
                            <div class="truncate mr-2">
                                <div class="font-bold text-white text-xs truncate">{{ $item->name }}</div>
                                <div class="text-[10px] text-slate-400">SKU: {{ $item->sku }}</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                    {{ number_format($item->total_sold, 0, ',', '.') }} {{ $item->unit_symbol }}
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-slate-500 italic">Belum ada data penjualan 30 hari terakhir.</div>
                    @endforelse
                </div>
            </div>

            <!-- Dead Stock & Warning List (1 Col) -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Peringatan Dead Stock (>90 Hari)</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($velocity['dead_stock'] as $dead)
                        <div class="p-3 rounded-xl bg-rose-500/5 border border-rose-500/20 flex items-center justify-between">
                            <div class="truncate mr-2">
                                <div class="font-bold text-slate-200 text-xs truncate">{{ $dead->name }}</div>
                                <div class="text-[10px] text-rose-400">Tidak ada penjualan: {{ round($dead->days_inactive) }} hari</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs font-bold text-slate-300">{{ $dead->stocks->sum('quantity') }} unit</span>
                                <span class="block text-[10px] text-slate-500">Stok Mengendap</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-emerald-400 italic">
                            <svg class="w-8 h-8 text-emerald-400 mx-auto mb-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Tidak ada barang kategori dead stock saat ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Ledger Transactions Stream -->
        <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-xl backdrop-blur-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Aktivitas Mutasi Terkini (Live Ledger Activity)</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">8 transaksi fisik masuk, keluar, dan transfer terakhir</p>
                </div>
                <a href="{{ route('inventory.stock-card') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition">
                    Lihat Seluruh Kartu Stok &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Produk / SKU</th>
                            <th class="px-4 py-3">Gudang</th>
                            <th class="px-4 py-3 text-center">Tipe Transaksi</th>
                            <th class="px-4 py-3 text-right">Kuantitas</th>
                            <th class="px-4 py-3">Referensi / Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentTransactions as $tx)
                            <tr class="hover:bg-slate-800/20 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-400">
                                    {{ $tx->transaction_date->format('d M Y') }}
                                    <span class="text-[10px] text-slate-500 block">{{ $tx->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-white">
                                    {{ $tx->product?->name }}
                                    <span class="text-[10px] text-slate-500 block">SKU: {{ $tx->product?->sku }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">
                                    {{ $tx->warehouse?->name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $typeClasses = match($tx->transaction_type) {
                                            'PURCHASE', 'RETURN_IN', 'ADJUSTMENT_IN', 'TRANSFER_IN', 'INITIAL' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                            'SALE', 'RETURN_OUT', 'ADJUSTMENT_OUT', 'TRANSFER_OUT' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                            default => 'bg-slate-700/40 text-slate-300 border-slate-600/50',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $typeClasses }}">
                                        {{ $tx->transaction_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold {{ $tx->isStockIncrement() ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $tx->isStockIncrement() ? '+' : '-' }}{{ number_format($tx->quantity, 0, ',', '.') }} {{ $tx->product?->unit?->symbol }}
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    <span class="font-mono text-white text-xs">{{ $tx->reference_id ?: '-' }}</span>
                                    <span class="text-[10px] text-slate-500 block">By: {{ $tx->user?->name ?? 'System' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-500 italic">Belum ada aktivitas transaksi mutasi stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ApexCharts Initialization Script -->
    <script>
        function dashboardAnalytics() {
            return {
                init() {
                    this.$nextTick(() => {
                        this.renderDemandTrendChart();
                        this.renderStockMovementChart();
                        this.renderWarehouseDistributionChart();
                    });
                },
                renderDemandTrendChart() {
                    const el = document.querySelector("#demandTrendChart");
                    if (!el || typeof window.ApexCharts === 'undefined') return;

                    const options = {
                        series: @json($demandChart['series']),
                        chart: {
                            type: 'area',
                            height: 280,
                            toolbar: { show: false },
                            background: 'transparent',
                        },
                        colors: ['#6366f1'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.45,
                                opacityTo: 0.05,
                                stops: [20, 100]
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        xaxis: {
                            categories: @json($demandChart['labels']),
                            labels: {
                                style: { colors: '#94a3b8', fontSize: '11px' }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#94a3b8', fontSize: '11px' }
                            }
                        },
                        grid: {
                            borderColor: '#334155',
                            strokeDashArray: 3
                        },
                        tooltip: {
                            theme: 'dark'
                        }
                    };

                    new window.ApexCharts(el, options).render();
                },
                renderStockMovementChart() {
                    const el = document.querySelector("#stockMovementChart");
                    if (!el || typeof window.ApexCharts === 'undefined') return;

                    const options = {
                        series: @json($movementChart['series']),
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: { show: false },
                            background: 'transparent',
                        },
                        colors: ['#10b981', '#f43f5e'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '45%',
                                borderRadius: 6
                            },
                        },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 2, colors: ['transparent'] },
                        xaxis: {
                            categories: @json($movementChart['labels']),
                            labels: {
                                style: { colors: '#94a3b8', fontSize: '11px' }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#94a3b8', fontSize: '11px' }
                            }
                        },
                        legend: {
                            labels: { colors: '#cbd5e1' },
                            position: 'top'
                        },
                        grid: {
                            borderColor: '#334155',
                            strokeDashArray: 3
                        },
                        tooltip: {
                            theme: 'dark'
                        }
                    };

                    new window.ApexCharts(el, options).render();
                },
                renderWarehouseDistributionChart() {
                    const el = document.querySelector("#warehouseDistributionChart");
                    if (!el || typeof window.ApexCharts === 'undefined') return;

                    const labels = @json($warehouseChart['labels']);
                    const series = @json($warehouseChart['quantities']);

                    if (series.length === 0 || series.every(v => v === 0)) {
                        el.innerHTML = '<div class="text-xs text-slate-500 italic text-center py-10">Belum ada kuantitas stok terdaftar di gudang.</div>';
                        return;
                    }

                    const options = {
                        series: series,
                        labels: labels,
                        chart: {
                            type: 'donut',
                            height: 250,
                            background: 'transparent',
                        },
                        colors: ['#6366f1', '#10b981', '#38bdf8', '#f59e0b', '#a855f7'],
                        stroke: { colors: ['#0f172a'], width: 2 },
                        legend: {
                            position: 'bottom',
                            labels: { colors: '#cbd5e1' }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return Math.round(val) + "%";
                            }
                        },
                        tooltip: {
                            theme: 'dark'
                        }
                    };

                    new window.ApexCharts(el, options).render();
                }
            };
        }
    </script>
</x-app-layout>
