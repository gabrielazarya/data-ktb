<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KampusController;
use App\Http\Controllers\PemuridanUserController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::view('/', 'landing')->name('landing');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard setelah login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/superadmin/dashboard', [DashboardController::class, 'superadmin'])->name('superadmin.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/pkk/dashboard', [DashboardController::class, 'pkk'])->name('pkk.dashboard');
    Route::get('/akk/dashboard', [DashboardController::class, 'akk'])->name('akk.dashboard');
    Route::get('/dashboard/kampus', [DashboardController::class, 'kampus'])->name('dashboard.kampus');
    Route::post('/dashboard/kampus', [KampusController::class, 'store'])->name('dashboard.kampus.store');
    Route::put('/dashboard/kampus/{kampus}', [KampusController::class, 'update'])->name('dashboard.kampus.update');
    Route::delete('/dashboard/kampus/{kampus}', [KampusController::class, 'destroy'])->name('dashboard.kampus.destroy');
    Route::get('/dashboard/pengguna', [DashboardController::class, 'pengguna'])->name('dashboard.pengguna');
    Route::get('/dashboard/pemuridan', [DashboardController::class, 'pemuridan'])->name('dashboard.pemuridan');
    Route::post('/dashboard/pkk', [PemuridanUserController::class, 'storePkk'])->name('dashboard.pkk.store');
    Route::put('/dashboard/pkk/{user}', [PemuridanUserController::class, 'updatePkk'])->name('dashboard.pkk.update');
    Route::delete('/dashboard/pkk/{user}', [PemuridanUserController::class, 'destroyPkk'])->name('dashboard.pkk.destroy');
    Route::post('/dashboard/akk', [PemuridanUserController::class, 'storeAkk'])->name('dashboard.akk.store');
    Route::put('/dashboard/akk/{user}', [PemuridanUserController::class, 'updateAkk'])->name('dashboard.akk.update');
    Route::delete('/dashboard/akk/{user}', [PemuridanUserController::class, 'destroyAkk'])->name('dashboard.akk.destroy');
    Route::get('/dashboard/pohon-pemuridan', [DashboardController::class, 'pohon'])->name('dashboard.pohon');
});
