<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccessSwitchController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KampusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegioController;
use App\Http\Controllers\TreeGroupController;
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
    Route::get('/dashboard/profil', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::put('/dashboard/profil', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::put('/dashboard/profil/password', [ProfileController::class, 'updatePassword'])->name('dashboard.profile.password');
    Route::get('/dashboard/kampus', [DashboardController::class, 'kampus'])->name('dashboard.kampus');
    Route::get('/dashboard/kampus/{kampus}', [DashboardController::class, 'kampusDetail'])->name('dashboard.kampus.show');
    Route::post('/dashboard/kampus', [KampusController::class, 'store'])->name('dashboard.kampus.store');
    Route::put('/dashboard/kampus/{kampus}', [KampusController::class, 'update'])->name('dashboard.kampus.update');
    Route::delete('/dashboard/kampus/{kampus}', [KampusController::class, 'destroy'])->name('dashboard.kampus.destroy');
    Route::get('/dashboard/regio', [DashboardController::class, 'regio'])->name('dashboard.regio');
    Route::post('/dashboard/regio', [RegioController::class, 'store'])->name('dashboard.regio.store');
    Route::put('/dashboard/regio/{regio}', [RegioController::class, 'update'])->name('dashboard.regio.update');
    Route::get('/dashboard/pengguna', [DashboardController::class, 'pengguna'])->name('dashboard.pengguna');
    Route::post('/dashboard/pengguna', [AdminUserController::class, 'store'])->name('dashboard.pengguna.store');
    Route::put('/dashboard/pengguna/{user}', [AdminUserController::class, 'update'])->name('dashboard.pengguna.update');
    Route::delete('/dashboard/pengguna/{user}', [AdminUserController::class, 'destroy'])->name('dashboard.pengguna.destroy');
    Route::post('/dashboard/pengguna/{user}/akses', [AccessSwitchController::class, 'switchToAdmin'])->name('dashboard.pengguna.switch-access');
    Route::post('/dashboard/akses/kembali', [AccessSwitchController::class, 'returnToSuperAdmin'])->name('dashboard.access.return');
    Route::get('/dashboard/anggota-ktb', [DashboardController::class, 'anggotaKtb'])->name('dashboard.anggota-ktb');
    Route::get('/dashboard/pohon-pemuridan', [DashboardController::class, 'pohon'])->name('dashboard.pohon');
    Route::post('/dashboard/pohon-pemuridan/kelompok', [TreeGroupController::class, 'storeGroup'])->name('dashboard.pohon.kelompok.store');
    Route::post('/dashboard/pohon-pemuridan/anggota', [TreeGroupController::class, 'storeMember'])->name('dashboard.pohon.anggota.store');
});
