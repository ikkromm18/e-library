<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_aktif_bisa_login(): void
    {
        $user = User::factory()->petugas()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->petugas()->create([
            'email' => 'inactive@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_petugas_tidak_bisa_akses_pengguna(): void
    {
        $user = User::factory()->petugas()->create();

        $response = $this->actingAs($user)->get('/pengguna');

        $response->assertForbidden();
    }

    public function test_admin_bisa_akses_pengguna(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/pengguna');

        $response->assertOk();
    }
}
