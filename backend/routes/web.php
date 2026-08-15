<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Employee list page
Route::view('employees', 'pages.employees.index')
    ->middleware(['auth', 'verified'])
    ->name('employees.index');

require __DIR__.'/auth.php';
