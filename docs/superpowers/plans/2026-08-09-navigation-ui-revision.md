# Navigation UI Revision Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the flat 11-item navigation bar into a modern, grouped, responsive navigation bar with icons, sticky backdrop-blur header, and role-based access.

**Architecture:** Re-architect `resources/views/layouts/navigation.blade.php` to group items into 5 logical categories (Dashboard, Katalog, Sirkulasi, Anggota, Laporan, Sistem) using Livewire/Blade dropdowns and Alpine.js.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS (CSS variables theme), Alpine.js.

## Global Constraints
- Must preserve all active route checks (`routeIs('buku.*')`, `routeIs('kategori.*')`, etc.).
- Must respect `@can('manage-system')` gate for Pengguna & Setting.
- Must use existing theme colors (`bg-surface`, `text-text-primary`, `text-text-secondary`, `bg-accent-soft`, `text-accent`, etc.).
- UI text in Indonesian.

---

### Task 1: Create Navigation Feature Tests

**Files:**
- Create: `tests/Feature/NavigationTest.php`

- [ ] **Step 1: Write the navigation feature test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_see_standard_navigation_links(): void
    {
        $user = User::factory()->petugas()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Katalog');
        $response->assertSee('Buku');
        $response->assertSee('Kategori');
        $response->assertSee('Rak');
        $response->assertSee('Sirkulasi');
        $response->assertSee('Peminjaman');
        $response->assertSee('Peminjam per Siswa');
        $response->assertSee('Pengembalian');
        $response->assertSee('Anggota');
        $response->assertSee('Laporan');
        $response->assertDontSee('Setting');
    }

    public function test_admin_can_see_system_settings_links(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sistem');
        $response->assertSee('Pengguna');
        $response->assertSee('Setting');
    }
}
```

- [ ] **Step 2: Run test to verify initial state**

Run: `vendor/bin/phpunit tests/Feature/NavigationTest.php`
Expected: Test passes or fails depending on text match (e.g. "Katalog", "Sirkulasi", "Sistem" dropdown labels don't exist yet).

---

### Task 2: Implement Revised Navigation Component

**Files:**
- Modify: `resources/views/layouts/navigation.blade.php`

- [ ] **Step 1: Update navigation.blade.php with modern UI/UX design**
  - Add sticky backdrop-blur header.
  - Implement grouped dropdown menus for Katalog (Buku, Kategori, Rak), Sirkulasi (Peminjaman, Peminjam per Siswa, Pengembalian), and Sistem (Pengguna, Setting).
  - Add inline SVG icons for every item.
  - Add user avatar initial badge & role tag in desktop dropdown.
  - Re-architect mobile menu into organized sections.

- [ ] **Step 2: Run feature tests**

Run: `vendor/bin/phpunit tests/Feature/NavigationTest.php`
Expected: PASS

- [ ] **Step 3: Run entire test suite**

Run: `vendor/bin/phpunit`
Expected: 114 passed tests.
