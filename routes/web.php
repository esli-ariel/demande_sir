<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\DemandePieceJointeController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;

// Routes publiques / auth
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
Route::middleware(['auth', 'role:demandeur'])->group(function () {
    Route::get('/demandeur', [DemandeController::class, 'dashboardDemandeur'])->name('dashboard.demandeur');
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
    Route::resource('users', UserController::class)
    ;

    // Vue globale sur toutes les demandes
    Route::get('/admin/demandes', [DemandeController::class, 'all'])
        ->name('admin.demandes.all');
});
Route::post('/demandes/{demande}/submit', [DemandeController::class, 'submit'])
    ->name('demandes.submit')
    ->middleware(['auth', 'role:demandeur']);


Route::get('/dashboard', [DashboardController::class, 'admin'])
    ->name('dashboard.admin')
    ->middleware(['auth', 'role:admin']);


// =====================
// Routes workflow 
// =====================
Route::middleware(['auth'])->group(function () {
    Route::resource('demandes', DemandeController::class);

    // Workflow
    Route::post('/demandes/{demande}/validerExploitation', [WorkflowController::class, 'validerExploitation'])->name('demandes.validerExploitation');
    Route::post('/demandes/{demande}/rejeterExploitation', [WorkflowController::class, 'rejeterExploitation'])->name('demandes.rejeterExploitation');

    Route::post('/demandes/{demande}/valider-dts', [WorkflowController::class, 'validerDts'])->name('demandes.valider_dts');
    Route::post('/demandes/{demande}/rejeter-dts', [WorkflowController::class, 'rejeterDts'])->name('demandes.rejeter_dts');


    Route::post('/demandes/{demande}/valider-structure', [WorkflowController::class, 'validerOuRejeterStructure'])->name('demandes.validerStructure')->middleware('role:structure_specialisee');


    Route::post('/demandes/{demande}/valider-controle', [WorkflowController::class, 'validerControle'])->name('demandes.valider_controle');
    Route::post('/demandes/{demande}/rejeter-controle', [WorkflowController::class, 'rejeterControle'])->name('demandes.rejeter_controle');

    Route::post('/demandes/{demande}/traiter-agent', [WorkflowController::class, 'traiterAgent'])->name('demandes.traiter_agent');
    Route::post('/demandes/{demande}/cloturer-reception', [WorkflowController::class, 'cloturerReception'])->name('demandes.cloturer_reception');
});
Route::post('/demandes/{demande}/valider-structure', [DemandeController::class, 'validerOuRejeterStructure'])
    ->name('demandes.validerStructure')
    ->middleware('role:structure_specialisee');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
Route::get('/dashboard/demandeur', [DashboardController::class, 'demandeur'])->name('dashboard.demandeur');
Route::get('/dashboard/exploitation', [DashboardController::class, 'exploitation'])->name('dashboard.exploitation');
Route::get('/dashboard/dts', [DashboardController::class, 'dts'])->name('dashboard.dts');
Route::get('/dashboard/structure', [DashboardController::class, 'structure'])->name('dashboard.structure');
Route::get('/dashboard/controle', [DashboardController::class, 'controle'])->name('dashboard.controle');
Route::get('/dashboard/service', [DashboardController::class, 'service'])->name('dashboard.service');
Route::get('/dashboard/reception', [DashboardController::class, 'reception'])->name('dashboard.reception');



Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('dashboard.admin');
});



Route::delete('/pieces/{piece}', [DemandePieceJointeController::class, 'destroy'])->name('pieces.destroy');

Route::middleware(['auth', 'role:exploitant'])->group(function () {
    Route::get('/exploitation/demandes', [DemandeController::class, 'indexExploitation'])
        ->name('demandes.index_exploitation');
});


Route::middleware(['auth', 'role:dts'])->group(function () {
    Route::get('/dts/demandes', [DemandeController::class, 'indexDTS'])
        ->name('demandes.index_dts');
});
Route::middleware(['auth', 'role:structure_specialisee'])->group(function () {
    Route::get('/structure/demandes', [DemandeController::class, 'indexStructure'])
        ->name('demandes.index_structure');
});
Route::middleware(['auth', 'role:controle_avancee'])->group(function () {
    Route::get('/controle/demandes', [DemandeController::class, 'indexControle'])
        ->name('demandes.index_controle');
});


Route::middleware(['auth', 'role:service_technique'])->group(function () {
    Route::get('/service/demandes', [DemandeController::class, 'indexServiceTechnique'])
        ->name('demandes.index_service');
});

Route::middleware(['auth', 'role:controle_avancée'])->group(function () {
    Route::get('/cloture/demandes', [DemandeController::class, 'indexCloture'])
        ->name('demandes.cloture');
});

//statistisques

Route::get('/dashboard/exploitation', [DashboardController::class, 'exploitation'])
    ->middleware(['auth', 'role:exploitant'])
    ->name('dashboard.exploitation');


Route::get('/dashboard/structure', [DashboardController::class, 'structure'])
    ->name('dashboard.structure')
    ->middleware(['auth', 'role:structure_specialisee']);



Route::get('/dashboard/dts', [DashboardController::class, 'dts'])
    ->name('dashboard.dts')
    ->middleware(['auth', 'role:dts']);

Route::get('/dashboard/controle', [DashboardController::class, 'controleAvance'])
    ->name('dashboard.controle')
    ->middleware(['auth', 'role:controle_avancee']);

Route::get('/dashboard/demandeur', [DashboardController::class, 'dashboardDemandeur'])
    ->middleware(['auth', 'role:demandeur'])
    ->name('dashboard.demandeur');

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');


//route cloture controle avancée
Route::middleware(['role:controle_avancee'])->group(function () {
    Route::get('/demandes/cloture', [DemandeController::class, 'indexCloture'])->name('demandes.cloture');
    Route::post('/demandes/{demande}/cloturer', [DemandeController::class, 'cloturer'])->name('demandes.cloturer');
});

//routes notification
Route::get('/notifications', function () {
    $notifications = auth()->user()->notifications;
    return view('notifications.index', compact('notifications'));
})->name('notifications.index');



require __DIR__.'/auth.php';
