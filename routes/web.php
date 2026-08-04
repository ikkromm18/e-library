<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Placeholder routes — will be replaced by Livewire components
    Route::get('/buku', fn () => view('dashboard'))->name('buku.index');
    Route::get('/kategori', fn () => view('dashboard'))->name('kategori.index');
    Route::get('/rak', fn () => view('dashboard'))->name('rak.index');
    Route::get('/anggota', fn () => view('dashboard'))->name('anggota.index');
    Route::get('/peminjaman', fn () => view('dashboard'))->name('peminjaman.index');
    Route::get('/pengembalian', fn () => view('dashboard'))->name('pengembalian.index');
    Route::get('/laporan', fn () => view('dashboard'))->name('laporan.index');
    Route::get('/pengguna', fn () => view('pengguna.index'))->middleware('can:manage-system')->name('pengguna.index');
    Route::get('/setting', fn () => view('dashboard'))->middleware('can:manage-system')->name('setting.index');
});

require __DIR__.'/auth.php';
