<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandePieceJointeController;
use App\Http\Controllers\DemandeurController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::pattern('demande', '[0-9]+');
Route::pattern('user', '[0-9]+');
Route::pattern('piece', '[0-9]+');
Route::pattern('notification', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}');

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('auth')->group(function () {
    Route::get('/archives', [\App\Http\Controllers\ArchiveController::class, 'index'])->name('archives.index');
    Route::get('/archives/{demande}', [\App\Http\Controllers\ArchiveController::class, 'show'])->name('archives.show');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('demandes', DemandeController::class);
    Route::delete('/pieces/{piece}', [DemandePieceJointeController::class, 'destroy'])->name('pieces.destroy');
    Route::get('/espace/demandes', [StaffController::class, 'index'])->name('staff.demandes');

    // Adresse historique commune : sélection de la file selon le rôle connecté.
    Route::get('/demandes/cloture', [StaffController::class, 'closures'])
        ->middleware('role:demandeur|controle_avancee|reception')->name('demandes.cloture');

    Route::middleware('role:admin')->group(function () {
        Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
        Route::resource('users', UserController::class)->except('show');
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/dashboard/admin', [AdminController::class, 'index'])->name('dashboard.admin');
        Route::get('/admin/demandes', [DemandeController::class, 'index'])->name('admin.demandes.all');
    });

    Route::middleware('role:demandeur')->group(function () {
        Route::get('/dashboard/demandeur', [DemandeurController::class, 'dashboard'])->name('dashboard.demandeur');
        Route::get('/demandeur', [DemandeurController::class, 'dashboard'])->name('demandeur.home');
        Route::get('/mes-demandes/reception', [DemandeurController::class, 'receptions'])->name('demandeur.receptions');
        Route::post('/demandes/{demande}/submit', [DemandeController::class, 'submit'])->name('demandes.submit');
        // Compatibilité des anciens formulaires : la clôture appartient à la réception.
        Route::post('/demandes/{demande}/receptionner', [DemandeurController::class, 'receptionner'])->name('demandes.receptionner');
        Route::post('/demandes/{demande}/cloture-demandeur', [DemandeurController::class, 'receptionner'])->name('demande.cloture.demandeur');
    });

    Route::middleware('role:exploitant')->group(function () {
        Route::get('/dashboard/exploitation', [StaffController::class, 'dashboard'])->name('dashboard.exploitation');
        Route::get('/exploitation/demandes', [StaffController::class, 'index'])->name('demandes.index_exploitation');
        Route::post('/demandes/{demande}/validerExploitation', [WorkflowController::class, 'validerExploitation'])->name('demandes.validerExploitation');
        Route::post('/demandes/{demande}/rejeterExploitation', [WorkflowController::class, 'rejeterExploitation'])->name('demandes.rejeterExploitation');
    });

    Route::middleware('role:dts')->group(function () {
        Route::get('/dashboard/dts', [StaffController::class, 'dashboard'])->name('dashboard.dts');
        Route::get('/dts/demandes', [StaffController::class, 'index'])->name('demandes.index_dts');
        Route::post('/demandes/{demande}/valider-dts', [WorkflowController::class, 'validerDts'])->name('demandes.valider_dts');
        Route::post('/demandes/{demande}/rejeter-dts', [WorkflowController::class, 'rejeterDts'])->name('demandes.rejeter_dts');
    });

    Route::middleware('role:structure_specialisee')->group(function () {
        Route::get('/dashboard/structure', [StaffController::class, 'dashboard'])->name('dashboard.structure');
        Route::get('/structure/demandes', [StaffController::class, 'index'])->name('demandes.index_structure');
        Route::post('/demandes/{demande}/valider-structure', [WorkflowController::class, 'validerOuRejeterStructure'])->name('demandes.validerStructure');
    });

    Route::middleware('role:controle_avancee')->group(function () {
        Route::get('/dashboard/controle', [StaffController::class, 'dashboard'])->name('dashboard.controle');
        Route::get('/controle/demandes', [StaffController::class, 'index'])->name('demandes.index_controle');
        Route::get('/cloture/demandes', [StaffController::class, 'index'])->name('controle.cloture');
        Route::post('/demandes/{demande}/valider-controle', [WorkflowController::class, 'validerControle'])->name('demandes.valider_controle');
        Route::post('/demandes/{demande}/rejeter-controle', [WorkflowController::class, 'rejeterControle'])->name('demandes.rejeter_controle');
        Route::post('/demandes/{demande}/cloturer', [WorkflowController::class, 'cloturer'])->name('demandes.cloturer');
        Route::post('/demandes/{demande}/cloture-controle', [WorkflowController::class, 'cloturer'])->name('demande.cloture.controle');
    });

    Route::middleware('role:service_technique')->group(function () {
        Route::get('/dashboard/service', [StaffController::class, 'dashboard'])->name('dashboard.service');
        Route::get('/service/demandes', [StaffController::class, 'index'])->name('demandes.index_service');
        Route::get('/service/executions', [StaffController::class, 'index'])->name('service.executions');
        Route::post('/demandes/{demande}/traiter-agent', [WorkflowController::class, 'traiterAgent'])->name('demandes.traiter_agent');
        Route::post('/demandes/{demande}/traiter', [WorkflowController::class, 'traiterAgent'])->name('demandes.traiter');
        Route::post('/demandes/{demande}/terminer-travaux', [WorkflowController::class, 'terminerTravaux'])->name('travaux.terminer');
    });

    Route::middleware('role:reception')->group(function () {
        Route::get('/dashboard/reception', [StaffController::class, 'dashboard'])->name('dashboard.reception');
        Route::get('/reception/demandes', [StaffController::class, 'index'])->name('reception.demandes');
        Route::post('/demandes/{demande}/reception-finale', [WorkflowController::class, 'cloturerReception'])->name('reception.cloturer');
        Route::post('/demandes/{demande}/cloturer-reception', [WorkflowController::class, 'cloturerReception'])->name('demandes.cloturer_reception');
    });

    Route::get('/dashboard/chef', [StaffController::class, 'dashboard'])->middleware('role:chef_structure')->name('dashboard.chef');

    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/summary', 'summary')->name('summary');
        Route::post('/read-all', 'readAll')->name('read_all');
        Route::post('/{notification}/read', 'read')->name('read');
        Route::post('/{notification}/open', 'open')->name('open');
    });

    // Circuit responsable_S retiré : ces URL ne peuvent plus changer une DMA.
    Route::middleware('role:responsable_S')->group(function () {
        Route::get('/responsable/validations', [ValidationController::class, 'index'])->name('responsable.validations');
        Route::post('/demandes/{demande}/valider', [ValidationController::class, 'valider'])->name('demandes.valider');
        Route::post('/demandes/{demande}/rejeter', [ValidationController::class, 'rejeter'])->name('demandes.rejeter');
    });
});

require __DIR__.'/auth.php';
