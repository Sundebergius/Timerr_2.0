<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\StripeController;
use Laravel\Socialite\Facades\Socialite;
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
});

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('auth/google/disconnect', [GoogleController::class, 'disconnect'])->name('google.disconnect');
Route::get('auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect'); // For connecting


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->get('/billing-portal', [ProfileController::class, 'redirectToBillingPortal'])->name('billing.portal');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Event management routes
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
        Route::put('/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/{id}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::get('/export', [EventController::class, 'exportToICS'])->name('events.export');
        Route::post('/import', [EventController::class, 'importFromICS'])->name('events.import');
        Route::get('/search', [EventController::class, 'search'])->name('events.search');
        Route::prefix('api')->group(function () {
            Route::get('/projects', [ProjectController::class, 'fetchProjects'])->name('api.projects');
            Route::get('/clients', [ClientController::class, 'fetchClients'])->name('api.clients');
        });
    });

    // Integrations Routes
    Route::prefix('integrations')->name('integrations.')->group(function () {
        // Webhooks Routes
        Route::prefix('webhooks')->name('webhooks.')->group(function () {
            Route::get('/', [WebhookController::class, 'index'])->name('index');
            Route::post('/', [WebhookController::class, 'store'])->name('store');
            Route::delete('/{webhook}', [WebhookController::class, 'destroy'])->name('destroy');
            Route::post('/trigger', [WebhookController::class, 'triggerWebhook'])->name('trigger');
            Route::post('/{webhook}/toggle', [WebhookController::class, 'toggleActive'])->name('toggle');
            Route::post('/client-status-updated', [WebhookController::class, 'handleClientStatusUpdated'])->name('client_status_updated');
            Route::post('/project-created', [WebhookController::class, 'handleProjectCreated'])->name('project_created');
            Route::post('/project-completed', [WebhookController::class, 'handleProjectCompleted'])->name('project_completed');
            Route::post('/task-created', [WebhookController::class, 'handleTaskCreated'])->name('task_created');
            Route::post('/task-completed', [WebhookController::class, 'handleTaskCompleted'])->name('task_completed');
            Route::post('/stripe/webhook', [WebhookController::class, 'handleStripeWebhook']);


        });
    });

    Route::middleware('auth')->group(function () {
        Route::get('/stripe/payment', [StripeController::class, 'showPaymentPage'])->name('stripe.payment');
        Route::post('/stripe/process', [StripeController::class, 'processPayment'])->name('stripe.process');
        Route::post('/subscription/resume', [StripeController::class, 'resumeSubscription'])->name('subscription.resume');

        // Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
        // Route::post('/subscription/downgrade', [SubscriptionController::class, 'downgrade'])->name('subscription.downgrade');
        // Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    });
    Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);


    // client tag routes
    // Route::post('/tag', [TagController::class, 'store']);
    // Route::delete('/tag/{id}', [TagController::class, 'delete']);
    // Route::get('/clients/{id}/tags', [TagController::class, 'getClientTags']);


    // Client management routes
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/', [ClientController::class, 'store'])->name('clients.store');
        Route::post('/import', [ClientController::class, 'import'])->name('clients.import');
        Route::get('/{client}', [ClientController::class, 'show'])->name('clients.show');
        // Route::put('/{client}/edit/notes', [ClientController::class, 'updateNote'])->name('clients.notes.update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.updateStatus');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::post('/clients/add-tag', [ClientController::class, 'addTag'])->name('clients.add-tag');
        Route::delete('/clients/remove-tag/{client}/{tag}', [ClientController::class, 'removeTag'])->name('clients.remove-tag');
        // Route::get('/', [ClientController::class, 'filterClients'])->name('clients.filter');
    });

    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        // Add other product routes here
    });

    // Invoice routes
    Route::prefix('invoices')->middleware(['auth'])->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/{id}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::patch('/{id}/updateStatus', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');
        Route::put('/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::patch('/{project}/toggleCompletion', [ProjectController::class, 'toggleCompletion'])->name('projects.toggleCompletion');
        //Route::get('/{project}/invoice', [ProjectController::class, 'invoice'])->name('projects.invoice');
        Route::get('/{project}/invoice', [ProjectController::class, 'invoice'])->name('projects.invoice');
        Route::post('/{project}/update-invoice-status', [ProjectController::class, 'updateInvoiceStatus']);
        Route::post('/{project}/update-client', [ProjectController::class, 'updateClient']);

        //note routes
        Route::prefix('/{project}/notes')->group(function () {
            Route::get('/create', [NoteController::class, 'create'])->name('projects.notes.create');
            Route::post('/', [NoteController::class, 'store'])->name('projects.notes.store');
            Route::get('/{note}', [NoteController::class, 'show'])->name('projects.notes.show');
            Route::get('/{note}/edit', [NoteController::class, 'edit'])->name('projects.notes.edit');
            Route::put('/{note}', [NoteController::class, 'update'])->name('projects.notes.update');
            Route::delete('/{note}', [NoteController::class, 'destroy'])->name('projects.notes.destroy');
        });

        // Contract routes
        Route::prefix('/{project}/contracts')->group(function () {
            Route::get('/create', [ContractController::class, 'create'])->name('projects.contracts.create');
            Route::post('/', [ContractController::class, 'store'])->name('projects.contracts.store'); // Add this
            Route::get('/{contract}', [ContractController::class, 'show'])->name('projects.contracts.show');
            Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('projects.contracts.edit');
            Route::put('/{contract}', [ContractController::class, 'update'])->name('projects.contracts.update'); // And this
            Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('projects.contracts.destroy');
        });

        // Task routes
        Route::prefix('/{project}/tasks')->group(function () {
            Route::get('/create', [TaskController::class, 'create'])->name('projects.tasks.create');
            Route::post('/', [TaskController::class, 'store'])->name('projects.tasks.store');
            Route::get('/{task}', [TaskController::class, 'show'])->name('projects.tasks.show');
            Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('projects.tasks.edit');
            Route::put('/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
            Route::post('/store-project', [TaskController::class, 'store'])->name('projects.tasks.storeProject');
            Route::post('/store-hourly', [TaskController::class, 'store'])->name('projects.tasks.storeHourly');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');
            Route::post('/store-product', [TaskController::class, 'createProductTask'])->name('projects.tasks.storeProduct');
            Route::post('/store-distance', [TaskController::class, 'createDistanceTask'])->name('projects.tasks.storeDistance');
            Route::post('/store-other', [TaskController::class, 'createOtherTask'])->name('projects.tasks.storeOther');
        });

        // registration routes
        Route::prefix('/{project}/tasks/{task}/registrations')->group(function () {
            Route::post('/store-project', [RegistrationController::class, 'storeProjectRegistration'])->name('projects.tasks.registrations.storeProject');
            Route::post('/store-hourly', [RegistrationController::class, 'storeHourlyRegistration'])->name('projects.tasks.registrations.storeHourly');
            Route::post('/store-distance', [RegistrationController::class, 'storeDistanceRegistration'])->name('projects.tasks.registrations.storeDistance'); 
            Route::get('/create', [RegistrationController::class, 'createRegistration'])->name('projects.tasks.registrations.create');
            Route::post('/store-product', [RegistrationController::class, 'storeProductRegistration'])->name('projects.tasks.registrations.storeProduct');
        });
    });
});

require __DIR__.'/auth.php';
