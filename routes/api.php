<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EventController;

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

// Chattie wants to test the getUserProducts method to work without the userId parameter
// Route::get('/products', [ProductController::class, 'getUserProducts']);
Route::get('/products/{userId}', [ProductController::class, 'getUserProducts']);

// Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/events', [EventController::class, 'index']);
    // Route::post('/events', [EventController::class, 'store']);
    // Route::put('/events/{event}', [EventController::class, 'update']);
    // Route::delete('/events/{event}', [EventController::class, 'destroy']);
// });

Route::post('/projects/{id}/send-to-dinero', [ProjectController::class, 'sendProjectToDinero']);
