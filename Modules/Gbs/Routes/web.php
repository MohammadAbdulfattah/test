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

Route::middleware(['setData','auth','SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->prefix('gbs')->group(function () {
    Route::get('install', [\Modules\Gbs\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [\Modules\Gbs\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', 'InstallController@uninstall');
    Route::get('tags', 'GbsController@tags')->name('tags');
    Route::post('/tag-store', 'GbsController@tagStore')->name('tags.store');
    Route::post('gbs/tags/{id}', 'GbsController@updateTag')->name('tags.update');

    Route::resource('/visites', 'VisitController');
    Route::resource('/failure-reasons', 'GbsFailureReasonController')->names('failure_reasons');
    Route::resource('/routes', 'RouteController');
    Route::get('/clients/search', 'RouteController@search')->name('gbs.clients.search');
    Route::get('/get-user-weekly-routes', 'RouteController@getWeeklyRoutes')->name('gbs.clients.routs');

    Route::get('routes/{id}/details', 'RouteController@details')->name('gbs.routes.details');

    Route::get('contacts-location', 'GbsController@contactsMap');
    Route::get('/user-performance', 'ReportController@delegatePerformanceReport');
    Route::get('/visits-report', 'ReportController@visitReport');
    Route::get('/shifts-report', 'ReportController@getShiftReport');
    Route::get('/search-customer', 'GbsController@search')->name('customers.search');
    Route::get('/reports/sectors', 'GbsController@getSectorStats');
     Route::get('/reports/tags', 'GbsController@getTagStats');
     Route::get('/reports/customer-groups', 'GbsController@getCustomerGroups');

});   
