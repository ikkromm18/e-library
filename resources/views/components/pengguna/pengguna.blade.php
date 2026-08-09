<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Pengguna</h1>
        <button wire:click="create" class="bg-accent hover:bg-accent-hover text-accent-fg px-4 py-2 rounded-lg text-sm font-medium">
            Tambah Pengguna
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Create Form --}}
    @if ($showCreate)
        <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Tambah Pengguna Baru</h2>
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Nama</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('name') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Email</label>
                        <input type="email" wire:model="email" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('email') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Role</label>
                        <select wire:model="role" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('role') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Password</label>
                        <input type="password" wire:model="password" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('password') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-accent hover:bg-accent-hover text-accent-fg px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                    <button type="button" wire:click="$set('showCreate', false)" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Batal</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Reset Password Modal --}}
    @if ($resetPasswordId)
        <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Reset Password</h2>
            <form wire:submit="savePassword">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-text-primary">Password Baru</label>
                    <input type="password" wire:model="newPassword" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    @error('newPassword') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-accent hover:bg-accent-hover text-accent-fg px-4 py-2 rounded-lg text-sm font-medium">Simpan Password</button>
                    <button type="button" wire:click="cancelReset" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Batal</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Users Table --}}
    <div class="bg-surface rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-surface divide-y divide-border">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-text-primary">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <select wire:change="editRole({{ $user->id }}, $event.target.value)" class="rounded-md border-border text-sm">
                                <option value="petugas" {{ $user->role === 'petugas' ? 'selected' : '' }}>Petugas</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="toggleActive({{ $user->id }})" class="px-2 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-success text-success-fg' : 'bg-danger-soft text-danger-fg' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="resetPassword({{ $user->id }})" class="text-accent hover:text-accent-hover">Reset Password</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-text-secondary">Belum ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t border-border flex items-center justify-between">
            <select wire:model.live="perPage" class="rounded-md border-border shadow-sm text-sm">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}">{{ $n }}</option>
                @endforeach
            </select>
            {{ $users->links() }}
        </div>
    </div>
</div>
