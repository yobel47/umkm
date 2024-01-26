<?php

use App\Http\Controllers\UmkmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/umkm', [UmkmController::class, 'index']);
Route::get('/umkm/search/{search}', [UmkmController::class, 'search']);
Route::get('/umkm/{id}', [UmkmController::class, 'detail']);
Route::post('/umkm', [UmkmController::class, 'store']);
Route::post('/umkm/{id}', [UmkmController::class, 'update']);
Route::delete('/umkm/{id}', [UmkmController::class, 'delete']);
