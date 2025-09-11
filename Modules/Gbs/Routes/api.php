<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/gbs', function (Request $request) {
    return $request->user();
});


Route::prefix('gbs')->group(function () {
    Route::post('/login', 'AuthController@login');
});


Route::prefix('gbs')->middleware('auth:api')->group(function () {

    Route::post('/start-shift', 'ShiftController@startShift');
    Route::post('/end-shift', 'ShiftController@endShift');
    Route::post('/start-visit', 'VisitController@startVisit');
    Route::post('/end-visit', 'VisitController@endVisit');
    Route::get('/daily-clients', 'RouteController@getTodayClients');
    Route::post('/contact/{contact}/location', 'VisitController@addContactLocation');
    Route::get('/daily-result', 'VisitController@visitStats');
     Route::get('/invoice-url', 'VisitController@invoiceUrl');


   
    
});
