<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CbrController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [CbrController::class, 'ShowIndex'])
    ->name('cbr.show');

Route::post('/', [CbrController::class, 'GetCurrencies'])
    ->name('cbr.send');

Route::get('/parse', [CbrController::class, 'GetData'])
    ->name('cbr.parse');

if (\App::environment('local') or \App::environment('staging')) {
    Route::any('/health', function () {
        // Cache testing
        \Illuminate\Support\Facades\Cache::put('testkey', 'testvalue', 1);

        return response()
            ->json([
                'status' => 'ok',
                'URL' => env('APP_URL'),
            ]);
    });
}
