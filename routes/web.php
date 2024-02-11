<?php

use Illuminate\Support\Facades\Route;

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
})->name('welcome');
Route::get('/api/credential',[\App\Http\Controllers\Amo::class,'authorized'])->name('authorized');
Route::post('/leads',[\App\Http\Controllers\Amo::class,'leads'])->name('leads');
Route::post('/update',[\App\Http\Controllers\Amo::class,'update'])->name('update');
Route::post('/update/lead',[\App\Http\Controllers\Amo::class,'updateLead'])->name('update.leads');

