<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\RegistrationController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client management routes
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/', [ClientController::class, 'store'])->name('clients.store');
        Route::post('/import', [ClientController::class, 'import'])->name('clients.import');
        Route::get('/{client}', [ClientController::class, 'show'])->name('clients.show');
        // Route::put('/{client}/edit/notes', [ClientController::class, 'updateNote'])->name('clients.notes.update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/{id}/status', [ClientController::class, 'updateStatus'])->name('clients.updateStatus');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/{client}', [ClientController::class, 'update'])->name('clients.update');
        // Route::get('/', [ClientController::class, 'filterClients'])->name('clients.filter');
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
        Route::get('/{project}/invoice', [ProjectController::class, 'invoice'])->name('projects.invoice');
        Route::post('/{project}/update-invoice-status', [ProjectController::class, 'updateInvoiceStatus']);
        Route::post('/{project}/update-client', [ProjectController::class, 'updateClient']);

        //note routes
        Route::prefix('/{project}/notes')->group(function () {
            Route::get('/create', [NoteController::class, 'create'])->name('projects.notes.create');
            Route::get('/{note}', [NoteController::class, 'show'])->name('projects.notes.show');
            Route::get('/{note}/edit', [NoteController::class, 'edit'])->name('projects.notes.edit');
            Route::delete('/{note}', [NoteController::class, 'destroy'])->name('projects.notes.destroy');
        });

        // Contract routes
        Route::prefix('/{project}/contracts')->group(function () {
            Route::get('/create', [ContractController::class, 'create'])->name('projects.contracts.create');
            Route::get('/{contract}', [ContractController::class, 'show'])->name('projects.contracts.show');
            Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('projects.contracts.edit');
            Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('projects.contracts.destroy');
        });

        // Task routes
        Route::prefix('/{project}/tasks')->group(function () {
            Route::get('/create', [TaskController::class, 'create'])->name('projects.tasks.create');
            Route::post('/', [TaskController::class, 'store'])->name('projects.tasks.store');
            Route::get('/{task}', [TaskController::class, 'show'])->name('projects.tasks.show');
            Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('projects.tasks.edit');
            Route::post('/store-project', [TaskController::class, 'store'])->name('projects.tasks.storeProject');
            Route::post('/store-hourly', [TaskController::class, 'store'])->name('projects.tasks.storeHourly');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');
            Route::post('/store-distance', [TaskController::class, 'createDistanceTask'])->name('projects.tasks.storeDistance');
        });

        // registration routes
        Route::prefix('/{project}/tasks/{task}/registrations')->group(function () {
            Route::post('/store-project', [RegistrationController::class, 'storeProjectRegistration'])->name('projects.tasks.registrations.storeProject');
            Route::post('/store-hourly', [RegistrationController::class, 'storeHourlyRegistration'])->name('projects.tasks.registrations.storeHourly');
            Route::post('/store-distance', [RegistrationController::class, 'storeDistanceRegistration'])->name('projects.tasks.registrations.storeDistance'); 
            Route::get('/create', [RegistrationController::class, 'createRegistration'])->name('projects.tasks.registrations.create');
        });
    });
});

require __DIR__.'/auth.php';
