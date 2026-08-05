<nav x-data="{ open: false }" class="bg-surface border-b border-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-text-primary" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('buku.index')" :active="request()->routeIs('buku.*')">
                        {{ __('Buku') }}
                    </x-nav-link>
                    <x-nav-link :href="route('kategori.index')" :active="request()->routeIs('kategori.*')">
                        {{ __('Kategori') }}
                    </x-nav-link>
                    <x-nav-link :href="route('rak.index')" :active="request()->routeIs('rak.*')">
                        {{ __('Rak') }}
                    </x-nav-link>
                    <x-nav-link :href="route('anggota.index')" :active="request()->routeIs('anggota.*')">
                        {{ __('Anggota') }}
                    </x-nav-link>
                    <x-nav-link :href="route('peminjaman.index')" :active="request()->routeIs('peminjaman.*')">
                        {{ __('Peminjaman') }}
                    </x-nav-link>
                    <x-nav-link :href="route('pengembalian.index')" :active="request()->routeIs('pengembalian.*')">
                        {{ __('Pengembalian') }}
                    </x-nav-link>
                    <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                        {{ __('Laporan') }}
                    </x-nav-link>
                    @can('manage-system')
                        <x-nav-link :href="route('pengguna.index')" :active="request()->routeIs('pengguna.*')">
                            {{ __('Pengguna') }}
                        </x-nav-link>
                        <x-nav-link :href="route('setting.index')" :active="request()->routeIs('setting.*')">
                            {{ __('Setting') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="flex items-center me-4">
                    <x-theme-toggle />
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-text-secondary hover:text-text-primary focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-text-secondary hover:text-text-primary hover:bg-surface-muted focus:outline-none focus:bg-surface-muted focus:text-text-primary transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('buku.index')" :active="request()->routeIs('buku.*')">
                {{ __('Buku') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('kategori.index')" :active="request()->routeIs('kategori.*')">
                {{ __('Kategori') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rak.index')" :active="request()->routeIs('rak.*')">
                {{ __('Rak') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('anggota.index')" :active="request()->routeIs('anggota.*')">
                {{ __('Anggota') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('peminjaman.index')" :active="request()->routeIs('peminjaman.*')">
                {{ __('Peminjaman') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pengembalian.index')" :active="request()->routeIs('pengembalian.*')">
                {{ __('Pengembalian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                {{ __('Laporan') }}
            </x-responsive-nav-link>
            @can('manage-system')
                <x-responsive-nav-link :href="route('pengguna.index')" :active="request()->routeIs('pengguna.*')">
                    {{ __('Pengguna') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('setting.index')" :active="request()->routeIs('setting.*')">
                    {{ __('Setting') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="pt-4 pb-1 border-t border-border">
            <div class="px-4">
                <div class="font-medium text-base text-text-primary">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-text-secondary">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
