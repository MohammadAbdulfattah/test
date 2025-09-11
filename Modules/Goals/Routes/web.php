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

use Illuminate\Support\Facades\Route;
use Modules\Goals\Http\Controllers\GoalsController;
use Modules\Goals\Http\Controllers\GroupController;

Route::prefix('goals')->middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone',  'AdminSidebarMenu', 'CheckUserLogin', 'check.shift'])->group(function () {
    Route::get('/', 'GoalsController@index');
    Route::get('/groups', 'GroupController@index');
    Route::get('/groups/create', [GroupController::class,'create'])->name('group.create');
    Route::post('/groups/store', [GroupController::class,'store'])->name('group.store');
    Route::get('/groups/goal-product', [GoalsController::class,'createProductGoal'])->name('product-goal.create');
    Route::get('/groups/goal-brand', [GoalsController::class,'createBrandGoal'])->name('brand-goal.create');
    Route::get('/groups/goal-category', [GoalsController::class,'createCategoryGoal'])->name('category-goal.create');
    Route::get('/groups/create-goal/{id}', [GoalsController::class,'create'])->name('goal.create');
    Route::post('/groups/store-goal/{id}', [GoalsController::class, 'storeGoal'])->name('goal.store');
    Route::get('/groups/goal/{id}', [GoalsController::class,'index'])->name('goal.index');
    Route::post('/groups/goal/store', [GoalsController::class,'store']);
    Route::get('/products/search', [GoalsController::class, 'search'])->name('products.search');
    Route::get('/group-details/{id}', [GroupController::class, 'show'])->name('group.show');
    Route::get('/group-edit/{id}', [GroupController::class, 'edit'])->name('goal_group.edit');
    Route::post('/group-update/{id}', [GroupController::class, 'update'])->name('goal_group.update');
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('goal_group.delete');
});
Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin')->prefix('cashvan')->group(function () {
    Route::get('install', [Modules\Goals\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [Modules\Goals\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [Modules\Goals\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [Modules\Goals\Http\Controllers\InstallController::class, 'update']);
});
