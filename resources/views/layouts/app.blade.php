<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'Smart Inventory') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full antialiased text-slate-200 bg-slate-950" x-data="{ sidebarOpen: false, userDropdownOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-cloak
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm lg:hidden">
        </div>

        <!-- Sidebar Component -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 border-r border-slate-800/80 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col justify-between">
            
            <!-- Sidebar Header & Nav -->
            <div class="flex-1 overflow-y-auto px-4 py-5">
                <!-- App Logo -->
                <div class="flex items-center justify-between px-2 mb-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 shadow-md shadow-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-base font-bold text-white tracking-tight">Smart Inventory</span>
                            <span class="block text-[11px] text-slate-400 font-medium">Forecasting System</span>
                        </div>
                    </a>

                    <!-- Close button on mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1 text-sm font-medium">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Section: Master Data -->
                    @if(auth()->user()->hasAnyRole(['super_admin', 'warehouse_staff', 'purchasing', 'manager']))
                        <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Master Data</div>

                        <a href="{{ route('products.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('products.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('products.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Products</span>
                        </a>

                        @role('super_admin')
                            <a href="{{ route('categories.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('categories.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('categories.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span>Categories</span>
                            </a>

                            <a href="{{ route('units.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('units.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('units.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                </svg>
                                <span>Units</span>
                            </a>
                        @endrole

                        @if(auth()->user()->hasAnyRole(['super_admin', 'purchasing']))
                            <a href="{{ route('suppliers.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('suppliers.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('suppliers.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Suppliers</span>
                            </a>
                        @endif

                        @if(auth()->user()->hasAnyRole(['super_admin', 'warehouse_staff', 'manager']))
                            <a href="{{ route('warehouses.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('warehouses.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('warehouses.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Warehouses</span>
                            </a>
                        @endif
                    @endif

                    <!-- Section: Inventory Operations -->
                    @if(auth()->user()->hasAnyRole(['super_admin', 'warehouse_staff', 'manager']))
                        <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Inventory Core</div>

                        <a href="{{ route('inventory.overview') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('inventory.overview', 'inventory.stock-in*', 'inventory.stock-out*', 'inventory.transfer*', 'inventory.adjustment*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('inventory.overview', 'inventory.stock-in*', 'inventory.stock-out*', 'inventory.transfer*', 'inventory.adjustment*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Stock Overview</span>
                        </a>

                        <a href="{{ route('inventory.stock-card') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('inventory.stock-card*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('inventory.stock-card*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Stock Card</span>
                        </a>
                    @endif

                    <!-- Section: Commercial (Purchasing & Sales) -->
                    @if(auth()->user()->hasAnyRole(['super_admin', 'purchasing', 'warehouse_staff', 'manager']))
                        <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Commercial & Orders</div>

                        @if(auth()->user()->hasAnyRole(['super_admin', 'purchasing', 'warehouse_staff', 'manager']))
                            <a href="{{ route('purchasing.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('purchasing.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('purchasing.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Purchasing (PO)</span>
                            </a>
                        @endif

                        @if(auth()->user()->hasAnyRole(['super_admin', 'warehouse_staff', 'manager']))
                            <a href="{{ route('sales.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('sales.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('sales.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Sales Orders</span>
                            </a>
                        @endif
                    @endif


                    <!-- Section: Forecasting & Replenishment -->
                    @if(auth()->user()->hasAnyRole(['super_admin', 'manager', 'purchasing']))
                        <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Forecasting & Restock</div>

                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                            <span>Demand Forecasting</span>
                        </a>

                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Restock Recommendations</span>
                        </a>
                    @endif

                    <!-- Section: Reports -->
                    @if(auth()->user()->hasAnyRole(['super_admin', 'manager']))
                        <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Reports</div>

                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Analytics & Export</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- User Footer Card -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-900/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700/80 flex items-center justify-center font-bold text-indigo-400 text-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->roles->first()?->name ?? 'User' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-950">
            <!-- Top Navigation Bar -->
            <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 z-30">
                <div class="px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
                    <!-- Mobile hamburger toggle & title -->
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-bold text-white tracking-tight">{{ $header ?? 'Dashboard' }}</h1>
                    </div>

                    <!-- Right Top Actions (Role Badge & User Dropdown) -->
                    <div class="flex items-center gap-3">
                        <!-- Role Badge -->
                        @php
                            $role = auth()->user()->roles->first()?->slug ?? 'user';
                            $roleBadgeClasses = match($role) {
                                'super_admin' => 'bg-purple-500/15 text-purple-300 border-purple-500/30',
                                'warehouse_staff' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                                'purchasing' => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
                                'manager' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                default => 'bg-slate-700/50 text-slate-300 border-slate-600',
                            };
                        @endphp
                        <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $roleBadgeClasses }}">
                            {{ auth()->user()->roles->first()?->name ?? 'User' }}
                        </span>

                        <!-- User Profile Menu -->
                        <div class="relative" @click.away="userDropdownOpen = false">
                            <button @click="userDropdownOpen = !userDropdownOpen"
                                    class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-800/80 transition text-slate-300 hover:text-white">
                                <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-300 flex items-center justify-center font-bold text-xs">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Box -->
                            <div x-show="userDropdownOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-52 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl py-1.5 z-50 text-sm">
                                <div class="px-4 py-2 border-b border-slate-800 text-xs">
                                    <div class="font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-slate-400 truncate">{{ auth()->user()->email }}</div>
                                </div>

                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>Pengaturan Profil</span>
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-rose-400 hover:bg-rose-500/10 transition text-left">
                                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- Global Alerts & Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>
</body>
</html>
