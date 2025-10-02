<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Route::get('/', function () {
//    return view('dashboard');
//})->name('dashboard');



Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('demandes', DemandeController::class);
});

// Gestion des utilisateurs (admin uniquement)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});



// Page d'accueil -> redirige vers dashboard
//Route::get('/', function () {
  //  return auth()->check() ? redirect()->route('dashboard') : redirect()->route('auth.login');
//});
// Dashboard (tous les utilisateurs authentifiés, redirection par rôle gérée dans DashboardController)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// =====================
// Routes DEMANDEUR
// =====================
Route::middleware(['auth', 'role:demandeur'])->group(function () {
    // CRUD demandes
    Route::resource('demandes', DemandeController::class)->except(['destroy']); 
    // un demandeur ne peut pas supprimer, à adapter selon ton besoin
});

// =====================
// Routes RESPONSABLE
// =====================
Route::middleware(['auth', 'role:responsable_S'])->group(function () {
    // Liste des demandes à valider
    Route::get('/responsable/validations', [ValidationController::class, 'index'])
        ->name('responsable.validations');

    // Actions validation / rejet
    Route::post('/demandes/{demande}/valider', [ValidationController::class, 'valider'])
        ->name('demandes.valider');
    Route::post('/demandes/{demande}/rejeter', [ValidationController::class, 'rejeter'])
        ->name('demandes.rejeter');
});

// =====================
// Routes SERVICE TECHNIQUE
// =====================
Route::middleware(['auth', 'role:service_technique'])->group(function () {
    // Liste des demandes à exécuter
    Route::get('/service/executions', [ValidationController::class, 'executions'])
        ->name('service.executions');

    // Exécuter une demande
    Route::post('/demandes/{demande}/traiter', [ValidationController::class, 'traiter'])
        ->name('demandes.traiter');

        // cloturer une demande
    Route::post('/demandes/{demande}/cloturer', [ValidationController::class, 'cloturer'])
        ->name('demandes.cloturer');
});

// =====================
// Routes ADMIN
// =====================
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Gestion utilisateurs
    Route::resource('users', UserController::class);

    // Vue globale sur toutes les demandes
    Route::get('/admin/demandes', [DemandeController::class, 'all'])
        ->name('admin.demandes.all');
});
Route::post('/demandes/{demande}/submit', [DemandeController::class, 'submit'])
    ->name('demandes.submit')
    ->middleware(['auth', 'role:demandeur']);

require __DIR__.'/auth.php';
