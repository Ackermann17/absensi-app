<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

// Mengubah route dashboard bawaan untuk menggunakan komponen Volt buatan kita
Volt::route('dashboard', 'admin.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Employee list page
Volt::route('employees', 'employees.index')
    ->middleware(['auth', 'verified'])
    ->name('employees.index');

// Terminal Absensi page
Volt::route('/terminal-absen', 'attendance.terminal')
    ->name('attendance.terminal');

Route::middleware(['auth', 'verified'])->group(function () {
    // Route lainnya...
    Volt::route('/leaves/approval', 'leaves.approval')->name('leaves.approval');
    Volt::route('/leaves/create', 'leaves.create')->name('leaves.create');
});

require __DIR__.'/auth.php';