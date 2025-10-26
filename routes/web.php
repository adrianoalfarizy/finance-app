<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
use App\Http\Controllers\{
    DashboardController, AccountController, AccountShareController,
    TransactionController, SavingController, SavingEntryController,
    DebtController, DebtPaymentController
};

Route::middleware(['auth'])->group(function () {
    // Dashboard (akun milik siapa via ?account_id=)
    Route::get('/', DashboardController::class)->name('dashboard');

    // Akun
    Route::resource('accounts', AccountController::class)->except(['show']);

    // Share akun
    Route::get('accounts/{account}/share', [AccountShareController::class,'edit'])->name('accounts.share.edit');
    Route::post('accounts/{account}/share', [AccountShareController::class,'update'])->name('accounts.share.update');
    Route::delete('accounts/{account}/share/{user}', [AccountShareController::class,'revoke'])->name('accounts.share.revoke');

    // Transaksi
    Route::resource('transactions', TransactionController::class)->only(['index','create','store','destroy']);

    // Tabungan
    Route::resource('savings', SavingController::class)->only(['index','create','store']);
    Route::post('savings/{saving}/entries', [SavingEntryController::class,'store'])->name('savings.entries.store');

    // Hutang
    Route::resource('debts', DebtController::class)->only(['index','create','store']);
    Route::post('debts/{debt}/payments', [DebtPaymentController::class,'store'])->name('debts.payments.store');
});

