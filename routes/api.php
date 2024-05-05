<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ProductController;

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

Route::post('/tag', [TagController::class, 'store']);
Route::delete('/tag/{id}', [TagController::class, 'delete']);
Route::get('/clients/{id}/tags', [TagController::class, 'getClientTags']);

Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{userId}', [ProductController::class, 'getUserProducts']);