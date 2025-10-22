<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\User;

class AdminController extends Controller
{
    //
    public function index()
    {
        // === Statistiques des demandes ===
        $totalDemandes = Demande::count();
        $demandesValidees = Demande::where('statut', 'like', 'validee%')->count();
        $demandesRefusees = Demande::where('statut', 'like', 'refusee%')->count();
        $demandesAttente = Demande::whereIn('statut', ['soumise', 'brouillon'])->count();

        // === Statistiques des utilisateurs ===
        $admins = User::role('admin')->count();
        $responsables = User::role('responsable_S')->count();
        $demandeurs = User::role('demandeur')->count();
        $agents = User::role('service_technique')->count();

        return view('admin.dashboard', compact(
            'totalDemandes',
            'demandesValidees',
            'demandesRefusees',
            'demandesAttente',
            'admins',
            'responsables',
            'demandeurs',
            'agents'
        ));
    }
}
