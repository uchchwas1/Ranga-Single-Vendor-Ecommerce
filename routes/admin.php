<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (separate "admin" guard)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('web')->group(function (): void {
    Route::middleware(['auth:admin', 'role:admin|super-admin'])->group(function (): void {
        Route::get('/', static function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });
});
