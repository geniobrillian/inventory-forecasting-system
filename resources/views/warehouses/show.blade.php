@extends('layouts.app', ['title' => 'Detail Gudang ' . $warehouse->name])

@section('content')
<div class="space-y-6" x-data="{ locationModalOpen: false, editModalOpen: false, editingLocation: {} }">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('warehouses.index') }}" class="hover:text-slate-200 transition">Gudang</a>
                <span>/</span>
                <span class="text-indigo-400 font-medium">{{ $warehouse->code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $warehouse->name }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $warehouse->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                    {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <p class="text-sm font-mono text-indigo-400 mt-0.5">Kode: {{ $warehouse->code }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('warehouses.edit', $warehouse) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700/80 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Gudang</span>
            </a>
            <button @click="locationModalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Sub-Lokasi</span>
            </button>
        </div>
    </div>

    <!-- Warehouse Info & Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Address & Details -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Lokasi & Alamat
            </h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Alamat Fisik</span>
                    <p class="text-slate-300 text-xs mt-1 leading-relaxed">{{ $warehouse->address ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider block">Deskripsi / Fasilitas</span>
                    <p class="text-slate-300 text-xs mt-1 leading-relaxed">{{ $warehouse->description ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Location Breakdown Stats -->
        <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-sm space-y-4 md:col-span-2">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Distribusi Sub-Lokasi Internal
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400 block">Total Sub-Lokasi</span>
                    <div class="text-2xl font-bold text-white mt-1">{{ number_format($locationStats['total']) }}</div>
                </div>
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400 block">Rak (Rack)</span>
                    <div class="text-2xl font-bold text-indigo-400 mt-1">{{ number_format($locationStats['racks']) }}</div>
                </div>
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400 block">Zona (Zone)</span>
                    <div class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($locationStats['zones']) }}</div>
                </div>
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400 block">Kotak / Bin</span>
                    <div class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($locationStats['bins']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Locations Table -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-sm">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white">Daftar Sub-Lokasi Gudang (Rak / Zona / Bin)</h3>
                <p class="text-xs text-slate-400">Pengelompokan posisi simpan fisik dalam area pergudangan.</p>
            </div>
            <button @click="locationModalOpen = true"
                    class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/80 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Sub-Lokasi</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="px-6 py-3.5">Kode Lokasi</th>
                        <th class="px-6 py-3.5">Nama Lokasi</th>
                        <th class="px-6 py-3.5">Tipe</th>
                        <th class="px-6 py-3.5">Keterangan</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($locations as $loc)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-indigo-500/10 border border-indigo-500/30 rounded-lg text-xs font-mono font-bold text-indigo-400">
                                    {{ $loc->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-white">{{ $loc->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold {{ $loc->type === 'RACK' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : ($loc->type === 'ZONE' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                    {{ $loc->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-400">{{ $loc->description ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('warehouses.locations.toggle-status', [$warehouse, $loc]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="Klik untuk mengubah status"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition {{ $loc->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $loc->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                        {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="editingLocation = {{ $loc->toJson() }}; editModalOpen = true"
                                            class="p-1.5 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded-lg transition"
                                            title="Edit Sub-Lokasi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <form action="{{ route('warehouses.locations.destroy', [$warehouse, $loc]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus sub-lokasi {{ $loc->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded-lg transition"
                                                title="Hapus Sub-Lokasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 text-xs">
                                Belum ada sub-lokasi internal (rak, zona, bin) yang terdaftar di gudang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($locations->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-950/40">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create Location -->
    <div x-show="locationModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.away="locationModalOpen = false"
             class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Tambah Sub-Lokasi Baru</h3>
                <button @click="locationModalOpen = false" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('warehouses.locations.store', $warehouse) }}" method="POST" class="space-y-4 mt-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Kode Lokasi <span class="text-rose-400">*</span></label>
                        <input type="text" name="code" required placeholder="RAK-A1" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm font-mono uppercase text-slate-200 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Tipe Lokasi <span class="text-rose-400">*</span></label>
                        <select name="type" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                            <option value="RACK">RACK (Rak)</option>
                            <option value="ZONE">ZONE (Zona)</option>
                            <option value="BIN">BIN (Kotak/Bin)</option>
                            <option value="AISLE">AISLE (Lorong)</option>
                            <option value="DEFAULT">DEFAULT (Umum)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Nama Lokasi / Rak <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" required placeholder="Rak Elektronik A Baris 1" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Keterangan (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Tingkat 1 sampai 4, kapasitas 500kg..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" @click="locationModalOpen = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-500 shadow-md">Simpan Sub-Lokasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Location -->
    <div x-show="editModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.away="editModalOpen = false"
             class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Edit Sub-Lokasi</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'{{ url('master-data/warehouses/' . $warehouse->id . '/locations') }}/' + editingLocation.id" method="POST" class="space-y-4 mt-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Kode Lokasi <span class="text-rose-400">*</span></label>
                        <input type="text" name="code" x-model="editingLocation.code" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm font-mono uppercase text-slate-200 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Tipe Lokasi <span class="text-rose-400">*</span></label>
                        <select name="type" x-model="editingLocation.type" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                            <option value="RACK">RACK (Rak)</option>
                            <option value="ZONE">ZONE (Zona)</option>
                            <option value="BIN">BIN (Kotak/Bin)</option>
                            <option value="AISLE">AISLE (Lorong)</option>
                            <option value="DEFAULT">DEFAULT (Umum)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Nama Lokasi / Rak <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" x-model="editingLocation.name" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Keterangan (Opsional)</label>
                    <textarea name="description" x-model="editingLocation.description" rows="2" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-500 shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
