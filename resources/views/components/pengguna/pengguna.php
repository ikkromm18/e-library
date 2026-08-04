<?php

use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    public $users;
    public $showCreate = false;
    public $editingId = null;
    public $resetPasswordId = null;

    #[Rule('required')]
    public $name = '';

    #[Rule('required|email|unique:users,email')]
    public $email = '';

    #[Rule('required|in:admin,petugas')]
    public $role = 'petugas';

    #[Rule('required|min:6')]
    public $password = '';

    public $newPassword = '';

    public function mount(): void
    {
        $this->loadUsers();
    }

    public function loadUsers(): void
    {
        $this->users = User::orderBy('role')->orderBy('name')->get();
    }

    public function create(): void
    {
        $this->resetCreateForm();
        $this->showCreate = true;
    }

    public function save(): void
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'password' => $this->password,
            'is_active' => true,
        ]);

        $this->showCreate = false;
        $this->loadUsers();
        session()->flash('success', 'Pengguna berhasil dibuat.');
    }

    public function editRole($userId, $role): void
    {
        $user = User::findOrFail($userId);
        $user->update(['role' => $role]);
        $this->loadUsers();
    }

    public function toggleActive($userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);
        $this->loadUsers();
    }

    public function resetPassword($userId): void
    {
        $this->resetPasswordId = $userId;
        $this->newPassword = '';
    }

    public function savePassword(): void
    {
        $this->validate(['newPassword' => 'required|min:6']);

        $user = User::findOrFail($this->resetPasswordId);
        $user->update(['password' => $this->newPassword]);

        $this->resetPasswordId = null;
        $this->newPassword = '';
        session()->flash('success', 'Password berhasil direset.');
    }

    public function cancelReset(): void
    {
        $this->resetPasswordId = null;
        $this->newPassword = '';
    }

    public function render()
    {
        return view('components.pengguna.pengguna');
    }

    private function resetCreateForm(): void
    {
        $this->resetValidation();
        $this->name = '';
        $this->email = '';
        $this->role = 'petugas';
        $this->password = '';
    }
};
