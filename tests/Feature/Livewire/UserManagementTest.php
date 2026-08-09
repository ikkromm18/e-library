<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_bisa_lihat_halaman_pengguna(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/pengguna');
        $response->assertOk();
    }

    public function test_admin_buat_petugas(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Livewire::new('pengguna'))
            ->call('create')
            ->set('name', 'Petugas Baru')
            ->set('email', 'petugas@example.com')
            ->set('role', 'petugas')
            ->set('password', 'password123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'petugas@example.com',
            'role' => 'petugas',
        ]);
    }

    public function test_toggle_nonaktif_user(): void
    {
        $user = User::factory()->petugas()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(Livewire::new('pengguna'))
            ->call('toggleActive', $user->id);

        $user->refresh();
        $this->assertFalse($user->is_active);
    }

    public function test_reset_password(): void
    {
        $user = User::factory()->petugas()->create();

        Livewire::actingAs($this->admin)
            ->test(Livewire::new('pengguna'))
            ->call('resetPassword', $user->id)
            ->set('newPassword', 'newpassword123')
            ->call('savePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(\Hash::check('newpassword123', $user->password));
    }
}
