<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StripeController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;



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

Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);

// Other webhooks for project completion (stateless)
Route::post('/webhooks/project-completed', [WebhookController::class, 'handleProjectCompleted']);
Route::post('/projects/{project}/send-webhook', [WebhookController::class, 'sendWebhook'])->name('projects.sendWebhook');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
});

Route::post('/tag', [TagController::class, 'store']);
Route::delete('/tag/{id}', [TagController::class, 'delete']);
Route::get('/clients/{id}/tags', [TagController::class, 'getClientTags']);

Route::post('/products', [ProductController::class, 'store']);

// Chattie wants to test the getUserProducts method to work without the userId parameter
// Route::get('/products', [ProductController::class, 'getUserProducts']);
Route::get('/products/{userId}', [ProductController::class, 'getUserProducts']);

// Apply Sanctum authentication middleware to the event routes
Route::middleware(['auth:sanctum'])->prefix('events')->group(function () {
    Route::get('/projects', [ProjectController::class, 'getProjects'])->name('api.projects.index');
    Route::get('/clients', [ClientController::class, 'getClients'])->name('api.clients.index');
    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::put('/{event}', [EventController::class, 'update']);
    Route::delete('/{event}', [EventController::class, 'destroy']);
});

Route::post('/projects/{id}/send-to-dinero', [ProjectController::class, 'sendProjectToDinero']);
