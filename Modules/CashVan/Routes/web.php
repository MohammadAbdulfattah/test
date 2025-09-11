<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\CashVan\Http\Controllers\CashVanController;

Route::prefix('cashvan')->middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone',  'AdminSidebarMenu', 'CheckUserLogin', 'check.shift'])->group(function () {
    Route::resource('/', CashVanController::class);
    Route::get('edit/{id}', [Modules\CashVan\Http\Controllers\CashVanController::class, 'edit'])->name('cashvan.edit');
    Route::put('update/{id}', [Modules\CashVan\Http\Controllers\CashVanController::class, 'update'])->name('cashvan.update');
    Route::delete('/{id}', [Modules\CashVan\Http\Controllers\CashVanController::class, 'destroy'])->name('cashvan.delete');
    Route::get('edit_van_stock/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'edit'])->name('van_stock.update');
    Route::put('edit_van_stock/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'update']);
    Route::get('add_van_stock/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'create'])->name('van_stock.create');
    Route::post('add_van_stock/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'store']);
    Route::get('add_main_stock', [Modules\CashVan\Http\Controllers\VanStockController::class, 'createMainStock']);
    Route::get('save_main_stock', [Modules\CashVan\Http\Controllers\VanStockController::class, 'saveMainStock']);
    Route::get('van_stock_report', [Modules\CashVan\Http\Controllers\VanStockController::class, 'getStockReport']);
    Route::get('show/stock/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'show']);
    Route::get('show/history/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'getVanHistory']);
    Route::get('scan/history/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'showHistory']);
    Route::delete('/empty-van_stock/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'destroy'])->name('van_stock.delete');
    Route::get('/create/stock-order/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'createStockOrder'])->name('van_stock.create_order');
    Route::post('/create/stock-order/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'storeVanOrder']);
    Route::get('/show/stock-order/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'showStockOrder']);
    Route::get('/response/stock-order/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'responseOnStockOrder']);
    Route::post('/edit/stock-order/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'updateStockOrder']);
    Route::get('/edit/stock-order/{id}', [Modules\CashVan\Http\Controllers\VanStockController::class, 'editStockOrder'])->name('van_stock.edit.order');
});
Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin')->prefix('cashvan')->group(function () {
    Route::get('install', [Modules\CashVan\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [Modules\CashVan\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [Modules\CashVan\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [Modules\CashVan\Http\Controllers\InstallController::class, 'update']);
});
