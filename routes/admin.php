<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\AssetsController;
use App\Http\Controllers\Admin\TemplatesController;
use App\Http\Controllers\Admin\CustomizationRequestsController;
use App\Http\Controllers\Admin\EscrowsController;
use App\Http\Controllers\Admin\SiteJobsController;
use App\Http\Controllers\Admin\FaqsController;
use App\Http\Controllers\Admin\ContactsController;
use App\Http\Controllers\Admin\WalletsController;
use App\Http\Controllers\Admin\WalletHistoriesController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\SubscriptionPlansController;
use App\Http\Controllers\Admin\GroupsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\NotesController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\OtpsController;
use App\Http\Controllers\Admin\DeletedUsersController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\AdminDatasController;
use App\Http\Controllers\Admin\AllGeneralSettingsController;
use App\Http\Controllers\Admin\GeneralSettingsController;

// Admin Auth
Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth', 'admin'])->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Users
        Route::resource('users', UsersController::class);
        Route::post('users/{user}/toggle-disable', [UsersController::class, 'toggleDisable'])->name('users.toggle-disable');

        // Deleted Users
        Route::resource('deleted-users', DeletedUsersController::class);


        // Assets
        Route::resource('assets', AssetsController::class);
        Route::post('assets/{asset}/toggle-pin', [AssetsController::class, 'togglePin'])->name('assets.toggle-pin');

        // Templates
        Route::resource('templates', TemplatesController::class);
        Route::post('templates/{template}/toggle-pin', [TemplatesController::class, 'togglePin'])->name('templates.toggle-pin');

        // Customization Requests
        Route::resource('customization-requests', CustomizationRequestsController::class);

        // Escrows
        Route::resource('escrows', EscrowsController::class);

        // Site Jobs
        Route::resource('site-jobs', SiteJobsController::class);


        // Subscriptions & Groups
        Route::resource('subscription-plans', SubscriptionPlansController::class);
        Route::resource('groups', GroupsController::class);
        Route::post('groups/{group}/toggle-pin', [GroupsController::class, 'togglePin'])->name('groups.toggle-pin');

        // Finance
        Route::resource('wallets', WalletsController::class);
        Route::resource('wallet-histories', WalletHistoriesController::class);
        
        // Withdrawals
        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('withdrawals/export-bank-csv', [WithdrawalController::class, 'exportBankCSV'])->name('withdrawals.export-bank-csv');
        Route::get('withdrawals/export-mobilemoney-csv', [WithdrawalController::class, 'exportMobileMoneyCSV'])->name('withdrawals.export-mobilemoney-csv');
        Route::post('withdrawals/mark-processed', [WithdrawalController::class, 'markProcessed'])->name('withdrawals.mark-processed');

        // Currencies
        Route::get('currencies', [\App\Http\Controllers\Admin\CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('currencies/create', [\App\Http\Controllers\Admin\CurrencyController::class, 'create'])->name('currencies.create');
        Route::post('currencies', [\App\Http\Controllers\Admin\CurrencyController::class, 'store'])->name('currencies.store');
        Route::get('currencies/{currency}/edit', [\App\Http\Controllers\Admin\CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('currencies/{currency}', [\App\Http\Controllers\Admin\CurrencyController::class, 'update'])->name('currencies.update');
        Route::delete('currencies/{currency}', [\App\Http\Controllers\Admin\CurrencyController::class, 'destroy'])->name('currencies.destroy');
        Route::get('currencies/update-rates', [\App\Http\Controllers\Admin\CurrencyController::class, 'updateExchangeRates'])->name('currencies.update-rates');

        // Others
        Route::resource('contacts', ContactsController::class);
        Route::resource('faqs', FaqsController::class);
        Route::resource('notes', NotesController::class);
        Route::resource('notifications', NotificationsController::class);
        Route::resource('otps', OtpsController::class);
        Route::resource('countries', CountriesController::class);

        // Settings
        Route::resource('roles', RolesController::class);
        Route::resource('admin-datas', AdminDatasController::class);

        Route::resource('all-general-settings', AllGeneralSettingsController::class);
        //route for settings
        Route::prefix('settings')->group(function () {
            Route::get('/{slug}', [GeneralSettingsController::class, 'index'])->name('settings.index');
            Route::get('/{slug}/add', [GeneralSettingsController::class, 'create'])->name('settings.create');
            Route::get('/{slug}/edit/{id}', [GeneralSettingsController::class, 'edit'])->name('settings.edit');
            Route::post('/{slug}/store', [GeneralSettingsController::class, 'store'])->name('settings.store');
            Route::post('/{slug}/update/{id}', [GeneralSettingsController::class, 'update'])->name('settings.update');
            Route::post('/{slug}/destroy/{id}', [GeneralSettingsController::class, 'destroy'])->name('settings.destroy');
        });
    });
});
