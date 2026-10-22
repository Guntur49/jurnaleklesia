<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JournalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/jurnal', [JournalController::class, 'index'])->name('journals.index');
    Route::post('/jurnal', [JournalController::class, 'store'])
        ->middleware('role:staff')
        ->name('journals.store');
    Route::get('/jurnal/{user}/pdf', [JournalController::class, 'pdf'])
        ->middleware('role:admin,owner')
        ->name('journals.pdf');

    Route::middleware('role:admin')->prefix('akun')->name('accounts.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::put('/{user}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{user}', [AccountController::class, 'destroy'])->name('destroy');
    });
});
