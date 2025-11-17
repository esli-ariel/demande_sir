<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    //
    public function index()
    {
           // ⚠️ Sécurité : éviter erreurs si pas encore de données
        $user = auth()->user();

    // Statistiques principales
    $totalDemandes = \App\Models\Demande::count();
    $demandesValidees = \App\Models\Demande::where('statut', 'cloturee_receptionnee')->count();
    $demandesRefusees = \App\Models\Demande::wherein('statut', ['refusee','refusee_exploitation','refusee_dts','refusee_structure_specialisee','refusee_controle_avancee','terminee_agent'])->count();
    $demandesAttente = \App\Models\Demande::wherein('statut', ['en_attente','soumise','validee_exploitation','validee_dts','validee_structure_specialisee','validee_controle_avancee'])->count();

    // Statistiques utilisateurs
    $admins = \App\Models\User::role('admin')->count();
    $responsables = \App\Models\User::role('exploitant')->count();
    $demandeurs = \App\Models\User::role('demandeur')->count();
    $agents = \App\Models\User::role('structure_specialisee')->count();

    // 🔥 Nouvelle section : évolution mensuelle
    $evolutionMensuelle = \App\Models\Demande::select(
            DB::raw('MONTH(created_at) as mois'),
            DB::raw('COUNT(*) as total')
        )
        ->whereYear('created_at', Carbon::now()->year)
        ->groupBy('mois')
        ->orderBy('mois')
        ->get();

    // Préparer les données pour Chart.js
    $labels = [];
    $data = [];

    // Générer les mois de Janvier à Décembre
    for ($m = 1; $m <= 12; $m++) {
        $labels[] = Carbon::create()->month($m)->translatedFormat('M'); // ex: Jan, Fév, Mar
        $count = $evolutionMensuelle->firstWhere('mois', $m)->total ?? 0;
        $data[] = $count;
    }

    // Récupérer toutes les demandes
    $demandes = \App\Models\Demande::with('user')->latest()->take(10)->get();

    return view('dashboards.admin', compact(
        'totalDemandes',
        'demandesValidees',
        'demandesRefusees',
        'demandesAttente',
        'admins',
        'responsables',
        'demandeurs',
        'agents',
        'demandes',
        'labels',
        'data'
    ));
    }
}
