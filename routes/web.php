<?php

use Illuminate\Support\Facades\Route;

Route::view('/docs/browser-support.html', 'static.docs.browser-support')->name('docs.browser_support');
Route::view('/docs/color-mode.html', 'static.docs.color-mode')->name('docs.color_mode');
Route::view('/docs/components/main-header.html', 'static.docs.components.main-header')->name('docs.components.main_header');
Route::view('/docs/components/main-sidebar.html', 'static.docs.components.main-sidebar')->name('docs.components.main_sidebar');
Route::view('/docs/faq.html', 'static.docs.faq')->name('docs.faq');
Route::view('/docs/how-to-contribute.html', 'static.docs.how-to-contribute')->name('docs.how_to_contribute');
Route::view('/docs/introduction.html', 'static.docs.introduction')->name('docs.introduction');
Route::view('/docs/javascript/pushmenu.html', 'static.docs.javascript.pushmenu')->name('docs.javascript.pushmenu');
Route::view('/docs/javascript/treeview.html', 'static.docs.javascript.treeview')->name('docs.javascript.treeview');
Route::view('/docs/layout.html', 'static.docs.layout')->name('docs.layout');
Route::view('/docs/license.html', 'static.docs.license')->name('docs.license');
Route::view('/examples/lockscreen.html', 'static.examples.lockscreen')->name('examples.lockscreen');
Route::view('/examples/login-v2.html', 'static.examples.login-v2')->name('examples.login_v2');
Route::view('/examples/login.html', 'static.examples.login')->name('examples.login');
Route::view('/examples/register-v2.html', 'static.examples.register-v2')->name('examples.register_v2');
Route::view('/examples/register.html', 'static.examples.register')->name('examples.register');
Route::view('/forms/general.html', 'static.forms.general')->name('forms.general');
Route::view('/generate/theme.html', 'static.generate.theme')->name('generate.theme');
Route::view('/index.html', 'static.index')->name('index');
Route::view('/index2.html', 'static.index2')->name('index2');
Route::view('/index3.html', 'static.index3')->name('index3');
Route::view('/layout/collapsed-sidebar.html', 'static.layout.collapsed-sidebar')->name('layout.collapsed_sidebar');
Route::view('/layout/fixed-complete.html', 'static.layout.fixed-complete')->name('layout.fixed_complete');
Route::view('/layout/fixed-footer.html', 'static.layout.fixed-footer')->name('layout.fixed_footer');
Route::view('/layout/fixed-header.html', 'static.layout.fixed-header')->name('layout.fixed_header');
Route::view('/layout/fixed-sidebar.html', 'static.layout.fixed-sidebar')->name('layout.fixed_sidebar');
Route::view('/layout/layout-custom-area.html', 'static.layout.layout-custom-area')->name('layout.layout_custom_area');
Route::view('/layout/layout-rtl.html', 'static.layout.layout-rtl')->name('layout.layout_rtl');
Route::view('/layout/logo-switch.html', 'static.layout.logo-switch')->name('layout.logo_switch');
Route::view('/layout/sidebar-mini.html', 'static.layout.sidebar-mini')->name('layout.sidebar_mini');
Route::view('/layout/unfixed-sidebar.html', 'static.layout.unfixed-sidebar')->name('layout.unfixed_sidebar');
Route::view('/tables/simple.html', 'static.tables.simple')->name('tables.simple');
Route::view('/UI/general.html', 'static.UI.general')->name('UI.general');
Route::view('/UI/icons.html', 'static.UI.icons')->name('UI.icons');
Route::view('/UI/timeline.html', 'static.UI.timeline')->name('UI.timeline');
Route::view('/widgets/cards.html', 'static.widgets.cards')->name('widgets.cards');
Route::view('/widgets/info-box.html', 'static.widgets.info-box')->name('widgets.info_box');
Route::view('/widgets/small-box.html', 'static.widgets.small-box')->name('widgets.small_box');

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PkkController;

// Landing Page
Route::view('/', 'landing')->name('landing');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard placeholders (akan diganti dengan controller penuh nanti)
Route::middleware(['auth'])->group(function () {
    Route::view('/superadmin/dashboard', 'superadmin.dashboard')->name('superadmin.dashboard');
    Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/pkk/dashboard', 'pkk.dashboard')->name('pkk.dashboard');
    Route::view('/akk/dashboard', 'akk.dashboard')->name('akk.dashboard');
    
    // Fitur Pengelolaan PKK oleh Admin
    Route::resource('pkk', PkkController::class);
});

