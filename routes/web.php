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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home route
Route::get('/', function () {
    return view('welcome');
});

// Google Auth Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::post('auth/google/disconnect', [GoogleController::class, 'disconnect'])->name('google.disconnect');
Route::get('auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Stripe Webhook Route (public, no auth required)
// Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard Route
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    // Billing and Subscription Routes
    Route::get('/billing-portal', [ProfileController::class, 'redirectToBillingPortal'])->name('billing.portal');
    Route::post('/subscribe', [StripeController::class, 'subscribe'])->name('stripe.subscribe');
    Route::get('/stripe/payment', [StripeController::class, 'showPaymentPage'])->name('stripe.payment');
    Route::post('/stripe/process', [StripeController::class, 'processPayment'])->name('stripe.process');
    Route::post('/subscription/resume', [StripeController::class, 'resumeSubscription'])->name('subscription.resume');

    /*
    |--------------------------------------------------------------------------
    | Profile Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Event Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
        Route::put('/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/{id}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::get('/export', [EventController::class, 'exportToICS'])->name('events.export');
        Route::post('/import', [EventController::class, 'importFromICS'])->name('events.import');
        Route::get('/search', [EventController::class, 'search'])->name('events.search');
    });

    /*
    |--------------------------------------------------------------------------
    | Client Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('clients')->group(function () {
        // Routes requiring subscription check middleware
        Route::middleware('check.subscription')->group(function () {
            Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
            Route::post('/', [ClientController::class, 'store'])->name('clients.store');
            Route::post('/import', [ClientController::class, 'import'])->name('clients.import');
        });

        // Routes without subscription check middleware
        Route::get('/', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::get('/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::post('/clients/add-tag', [ClientController::class, 'addTag'])->name('clients.add-tag');
        Route::delete('/clients/remove-tag/{client}/{tag}', [ClientController::class, 'removeTag'])->name('clients.remove-tag');
        Route::post('/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.updateStatus');
    });

    /*
    |--------------------------------------------------------------------------
    | Product Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->group(function () {
        // Apply middleware to product creation and storing routes
        Route::middleware('check.subscription')->group(function () {
            Route::get('/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/', [ProductController::class, 'store'])->name('products.store');
        });
    
        // No middleware needed for viewing or managing existing products
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Invoice Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/{id}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    });

    /*
    |--------------------------------------------------------------------------
    | Project and Task Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('projects')->group(function () {
        // Routes requiring subscription check middleware
        Route::middleware('check.subscription')->group(function () {
            Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        });
    
        // Other project-related routes
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
        // Toggle Completion Route (changed back to PATCH)
        Route::patch('/{project}/toggleCompletion', [ProjectController::class, 'toggleCompletion'])->name('projects.toggleCompletion');
    
        // Notes Routes for Projects
        Route::prefix('/{project}/notes')->group(function () {
            Route::get('/create', [NoteController::class, 'create'])->name('projects.notes.create');
            Route::post('/', [NoteController::class, 'store'])->name('projects.notes.store');
            Route::get('/{note}', [NoteController::class, 'show'])->name('projects.notes.show');
            Route::get('/{note}/edit', [NoteController::class, 'edit'])->name('projects.notes.edit');
            Route::put('/{note}', [NoteController::class, 'update'])->name('projects.notes.update');
            Route::delete('/{note}', [NoteController::class, 'destroy'])->name('projects.notes.destroy');
        });
    
        // Contracts Routes for Projects
        Route::prefix('/{project}/contracts')->group(function () {
            Route::get('/create', [ContractController::class, 'create'])->name('projects.contracts.create');
            Route::post('/', [ContractController::class, 'store'])->name('projects.contracts.store');
            Route::get('/{contract}', [ContractController::class, 'show'])->name('projects.contracts.show');
            Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('projects.contracts.edit');
            Route::put('/{contract}', [ContractController::class, 'update'])->name('projects.contracts.update');
            Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('projects.contracts.destroy');
        });
    
        // Task Routes for Projects
        Route::prefix('/{project}/tasks')->group(function () {
            Route::get('/create', [TaskController::class, 'create'])->name('projects.tasks.create');
            Route::post('/', [TaskController::class, 'store'])->name('projects.tasks.store');
            Route::get('/{task}', [TaskController::class, 'show'])->name('projects.tasks.show');
            Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('projects.tasks.edit');
            Route::put('/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');
            
            // Registration Routes for Tasks (back inside {task}/registrations)
            Route::prefix('/{task}/registrations')->group(function () {
                Route::post('/store-project', [RegistrationController::class, 'storeProjectRegistration'])->name('projects.tasks.registrations.storeProject');
                Route::post('/store-hourly', [RegistrationController::class, 'storeHourlyRegistration'])->name('projects.tasks.registrations.storeHourly');
                Route::post('/store-distance', [RegistrationController::class, 'storeDistanceRegistration'])->name('projects.tasks.registrations.storeDistance');
                Route::post('/store-product', [RegistrationController::class, 'storeProductRegistration'])->name('projects.tasks.registrations.storeProduct');
                Route::get('/create', [RegistrationController::class, 'createRegistration'])->name('projects.tasks.registrations.create');
            });
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('integrations')->name('integrations.')->group(function () {
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
        });
    });
});

// Include Auth Routes
require __DIR__.'/auth.php';
