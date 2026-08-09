@php $appLogo = \App\Models\Setting::get('logo'); @endphp
<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-surface/90 backdrop-blur-md border-b border-border shadow-xs theme-transition">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side: Brand & Main Navigation -->
            <div class="flex items-center">
                <!-- Brand Logo -->
                <div class="shrink-0 flex items-center me-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group focus:outline-none">
                        @if ($appLogo)
                            <img src="{{ str_starts_with($appLogo, 'upload/') ? asset($appLogo) : asset('storage/'.$appLogo) }}" class="block h-9 w-auto object-contain transition-transform duration-200 group-hover:scale-105" alt="Logo">
                        @else
                            <div class="p-1.5 bg-accent/10 rounded-xl text-accent transition-colors duration-200 group-hover:bg-accent/20">
                                <x-application-logo class="block h-7 w-auto fill-current" />
                            </div>
                        @endif
                        <span class="font-bold text-lg text-text-primary tracking-tight hidden md:inline-block">e-Library</span>
                    </a>
                </div>

                <!-- Desktop Navigation Items -->
                <div class="hidden sm:flex sm:items-center sm:space-x-1 lg:space-x-2">
                    <!-- Dashboard -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <svg class="w-4 h-4 me-1.5 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Katalog Dropdown -->
                    @php
                        $isKatalogActive = request()->routeIs(['buku.*', 'kategori.*', 'rak.*']);
                    @endphp
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ $isKatalogActive ? 'border-accent text-accent font-semibold' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }}">
                                <svg class="w-4 h-4 me-1.5 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                                <span>{{ __('Katalog') }}</span>
                                <svg class="ms-1 w-3.5 h-3.5 opacity-70 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-text-secondary opacity-70">
                                {{ __('Katalog Perpustakaan') }}
                            </div>
                            <x-dropdown-link :href="route('buku.index')" class="{{ request()->routeIs('buku.*') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                    <span>{{ __('Buku') }}</span>
                                </div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('kategori.index')" class="{{ request()->routeIs('kategori.*') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                    </svg>
                                    <span>{{ __('Kategori') }}</span>
                                </div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('rak.index')" class="{{ request()->routeIs('rak.*') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 4.179 2.25m-4.179-2.25 4.179-2.25m4.179 6.75-4.179-2.25m4.179 2.25 4.179 2.25m-4.179-2.25 4.179-2.25m-4.179-6.75-4.179 2.25m4.179-2.25 4.179 2.25m-4.179-2.25 4.179-2.25" />
                                    </svg>
                                    <span>{{ __('Rak Buku') }}</span>
                                </div>
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Sirkulasi Dropdown -->
                    @php
                        $isSirkulasiActive = request()->routeIs(['peminjaman.*', 'pengembalian.*']);
                    @endphp
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ $isSirkulasiActive ? 'border-accent text-accent font-semibold' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }}">
                                <svg class="w-4 h-4 me-1.5 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                                <span>{{ __('Sirkulasi') }}</span>
                                <svg class="ms-1 w-3.5 h-3.5 opacity-70 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-text-secondary opacity-70">
                                {{ __('Transaksi Perpustakaan') }}
                            </div>
                            <x-dropdown-link :href="route('peminjaman.index')" class="{{ request()->routeIs('peminjaman.index') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                                    </svg>
                                    <span>{{ __('Peminjaman') }}</span>
                                </div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('peminjaman.per-siswa')" class="{{ request()->routeIs('peminjaman.per-siswa') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6 0 3.375 3.375 0 0 1 6 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                    </svg>
                                    <span>{{ __('Peminjam per Siswa') }}</span>
                                </div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('pengembalian.index')" class="{{ request()->routeIs('pengembalian.*') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                    </svg>
                                    <span>{{ __('Pengembalian') }}</span>
                                </div>
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Anggota -->
                    <x-nav-link :href="route('anggota.index')" :active="request()->routeIs('anggota.*')">
                        <svg class="w-4 h-4 me-1.5 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6 0 3.375 3.375 0 0 1 6 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        {{ __('Anggota') }}
                    </x-nav-link>

                    <!-- Laporan -->
                    <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                        <svg class="w-4 h-4 me-1.5 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        {{ __('Laporan') }}
                    </x-nav-link>

                    <!-- Sistem Dropdown (Admin Only) -->
                    @can('manage-system')
                        @php
                            $isSistemActive = request()->routeIs(['pengguna.*', 'setting.*']);
                        @endphp
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ $isSistemActive ? 'border-accent text-accent font-semibold' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }}">
                                    <svg class="w-4 h-4 me-1.5 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <span>{{ __('Sistem') }}</span>
                                    <svg class="ms-1 w-3.5 h-3.5 opacity-70 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-text-secondary opacity-70">
                                    {{ __('Pengaturan Sistem') }}
                                </div>
                                <x-dropdown-link :href="route('pengguna.index')" class="{{ request()->routeIs('pengguna.*') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <span>{{ __('Pengguna') }}</span>
                                    </div>
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('setting.index')" class="{{ request()->routeIs('setting.*') ? 'bg-accent-soft text-accent font-semibold' : '' }}">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-accent opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h97.5M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m-9.75 0h9.75" />
                                        </svg>
                                        <span>{{ __('Setting') }}</span>
                                    </div>
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endcan
                </div>
            </div>

            <!-- Right Side: Theme Toggle & User Profile Menu -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Theme Toggle Button -->
                <div class="flex items-center">
                    <x-theme-toggle />
                </div>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-2.5 py-1.5 border border-transparent rounded-lg text-sm font-medium text-text-secondary hover:text-text-primary hover:bg-surface-muted focus:outline-none focus:bg-surface-muted transition duration-150 ease-in-out">
                            <!-- Avatar Initial Badge -->
                            <div class="w-8 h-8 rounded-full bg-accent/10 text-accent font-semibold text-xs flex items-center justify-center border border-accent/20">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <!-- User Name & Role -->
                            <div class="text-start leading-tight">
                                <div class="font-semibold text-text-primary text-sm max-w-[130px] truncate">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-text-secondary font-medium">
                                    @can('manage-system')
                                        <span class="inline-block px-1.5 py-0.2 rounded bg-accent-soft text-accent font-bold">Admin</span>
                                    @else
                                        <span class="inline-block px-1.5 py-0.2 rounded bg-surface-muted text-text-secondary font-medium">Petugas</span>
                                    @endcan
                                </div>
                            </div>
                            <svg class="w-4 h-4 opacity-60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile Header -->
                        <div class="px-4 py-3 border-b border-border">
                            <p class="text-xs text-text-secondary font-medium">{{ __('Masuk sebagai') }}</p>
                            <p class="text-sm font-semibold text-text-primary truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <!-- Dropdown Menu Links -->
                        <div class="py-1">
                            <x-dropdown-link :href="route('profile.edit')">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <span>{{ __('Profil Saya') }}</span>
                                </div>
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();" class="text-danger-solid hover:text-danger-solid hover:bg-danger-soft/20">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3h12.75" />
                                        </svg>
                                        <span>{{ __('Keluar (Log Out)') }}</span>
                                    </div>
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Button for Mobile -->
            <div class="-me-2 flex items-center sm:hidden gap-2">
                <x-theme-toggle />
                <button @click="open = ! open" type="button" class="inline-flex items-center justify-center p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-surface-muted focus:outline-none focus:bg-surface-muted focus:text-text-primary transition duration-150 ease-in-out" aria-label="Toggle Menu">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Mobile Drawer Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-surface border-b border-border shadow-lg">
        <div class="pt-2 pb-4 space-y-1.5 px-2">
            <!-- Section: Utama -->
            <div class="px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-text-secondary opacity-60">
                {{ __('Utama') }}
            </div>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    <span>{{ __('Dashboard') }}</span>
                </div>
            </x-responsive-nav-link>

            <!-- Section: Katalog -->
            <div class="px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-text-secondary opacity-60">
                {{ __('Katalog Perpustakaan') }}
            </div>
            <x-responsive-nav-link :href="route('buku.index')" :active="request()->routeIs('buku.*')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>{{ __('Buku') }}</span>
                </div>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('kategori.index')" :active="request()->routeIs('kategori.*')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    <span>{{ __('Kategori') }}</span>
                </div>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rak.index')" :active="request()->routeIs('rak.*')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 4.179 2.25m-4.179-2.25 4.179-2.25m4.179 6.75-4.179-2.25m4.179 2.25 4.179 2.25m-4.179-2.25 4.179-2.25m-4.179-6.75-4.179 2.25m4.179-2.25 4.179 2.25m-4.179-2.25 4.179-2.25" />
                    </svg>
                    <span>{{ __('Rak Buku') }}</span>
                </div>
            </x-responsive-nav-link>

            <!-- Section: Sirkulasi -->
            <div class="px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-text-secondary opacity-60">
                {{ __('Sirkulasi & Transaksi') }}
            </div>
            <x-responsive-nav-link :href="route('peminjaman.index')" :active="request()->routeIs('peminjaman.index')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                    </svg>
                    <span>{{ __('Peminjaman') }}</span>
                </div>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('peminjaman.per-siswa')" :active="request()->routeIs('peminjaman.per-siswa')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6 0 3.375 3.375 0 0 1 6 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>{{ __('Peminjam per Siswa') }}</span>
                </div>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pengembalian.index')" :active="request()->routeIs('pengembalian.*')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    <span>{{ __('Pengembalian') }}</span>
                </div>
            </x-responsive-nav-link>

            <!-- Section: Laporan & Anggota -->
            <div class="px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-text-secondary opacity-60">
                {{ __('Laporan & Keanggotaan') }}
            </div>
            <x-responsive-nav-link :href="route('anggota.index')" :active="request()->routeIs('anggota.*')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6 0 3.375 3.375 0 0 1 6 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>{{ __('Anggota') }}</span>
                </div>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <span>{{ __('Laporan') }}</span>
                </div>
            </x-responsive-nav-link>

            <!-- Section: Sistem (Admin Only) -->
            @can('manage-system')
                <div class="px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-text-secondary opacity-60">
                    {{ __('Pengaturan Sistem') }}
                </div>
                <x-responsive-nav-link :href="route('pengguna.index')" :active="request()->routeIs('pengguna.*')">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span>{{ __('Pengguna') }}</span>
                    </div>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('setting.index')" :active="request()->routeIs('setting.*')">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h97.5M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>{{ __('Setting') }}</span>
                    </div>
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Mobile User Options -->
        <div class="pt-4 pb-3 border-t border-border bg-surface-muted/30 px-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-accent/10 text-accent font-semibold flex items-center justify-center border border-accent/20">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div>
                    <div class="font-semibold text-base text-text-primary leading-tight">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-text-secondary">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span>{{ __('Profil Saya') }}</span>
                    </div>
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();" class="text-danger-solid">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3h12.75" />
                            </svg>
                            <span>{{ __('Keluar (Log Out)') }}</span>
                        </div>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
