<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
        return redirect()->route('login');
        }
        if ($user->hasRole('admin')) {
            $demandes = Demande::with('user')->latest()->get();
            return view('dashboards.admin', compact('demandes'));
        } 
        
       // elseif ($user->hasRole('demandeur')) {
        //    $demandes = $user->demandes()->latest()->get();
         //   return view('dashboards.demandeur', compact('demandes'));
       // }

        //$user->assignRole('demandeur');
   // return view('dashboards.demandeur');

        // Cas DEMANDEUR → il ne voit que ses propres demandes
        if ($user->hasRole('demandeur')) {
            $demandes = Demande::where('user_id', $user->id)->get();
            return view('dashboards.demandeur', compact('demandes'));
        }

        // Cas EXPLOITATION → il voit les demandes "soumise"
        if ($user->hasRole('exploitant')) {
            $demandes = Demande::where('statut', 'soumise')->get();
            return view('dashboards.exploitation', compact('demandes'));
            
        }

        // Cas DTS → il voit les demandes validées exploitation
        if ($user->hasRole('dts')) {
            $demandes = Demande::where('statut', 'validee_exploitation')->get();
            return view('dashboards.dts', compact('demandes'));
        }

        // Cas STRUCTURE SPÉCIALISÉE → il voit les demandes validées DTS
        if ($user->hasRole('structure_specialisee')) {
            $demandes = Demande::where('statut', 'validee_dts')->get();
            return view('dashboards.structures', compact('demandes'));
        }

        // Cas CONTRÔLE AVANCÉ → il voit les demandes validées par toutes les structures
        if ($user->hasRole('controle_avancee')) {
            $demandes = Demande::where('statut', 'validee_structure_specialisee')->get();
            return view('dashboards.controle', compact('demandes'));
        }

        // Cas SERVICE TECHNIQUE → il voit les DMA validées par contrôle avancé
        if ($user->hasRole('service_technique')) {
            $demandes = Demande::where('statut', 'validee_controle_avancee')->get();
            return view('dashboards.service', compact('demandes'));
        }

        // Cas RÉCEPTION → il voit les demandes terminées par les agents
        if ($user->hasRole('reception')) {
            $demandes = Demande::where('statut', 'en_cours_traitement')->get();
            return view('dashboards.reception', compact('demandes'));
        }

        // Fallback si aucun rôle ne correspond
        abort(403, 'Aucun dashboard trouvé pour votre rôle.');
    
}
public function dashboardDemandeur()
{
    $user = Auth::user();

    $demandes = Demande::where('user_id', $user->id)->latest()->get();

    // Statistiques calculées
    $stats = [
        'total'       => $demandes->count(),
        'en_attente'  => $demandes->whereIn('statut', ['soumise', 'en_attente_validation'])->count(),
        'validees'    => $demandes->whereIn('statut', [
            'validee_exploitation',
            'validee_dts',
            'validee_structure_specialisee',
            'validee_controle_avancee',
            'cloturee_receptionnee'
        ])->count(),
        'rejetees'    => $demandes->where(fn($d) => str_contains($d->statut, 'refusee'))->count(),
    ];

    return view('dashboards.demandeur', compact('demandes', 'stats'));
}
public function admin()
    {
        // ⚠️ Sécurité : éviter erreurs si pas encore de données
        $totalDemandes = Demande::count() ?? 0;

        $demandesValidees = Demande::where('statut', 'LIKE', '%validee%')->count() ?? 0;
        $demandesRefusees = Demande::where('statut', 'LIKE', '%refusee%')->count() ?? 0;
        $demandesAttente = Demande::whereIn('statut', ['soumise', 'brouillon'])->count() ?? 0;

        // Comptage des rôles (via Spatie)
        $admins = User::role('admin')->count() ?? 0;
        $responsables = User::role('responsable_S')->count() ?? 0;
        $demandeurs = User::role('demandeur')->count() ?? 0;
        $agents = User::role('service_technique')->count() ?? 0;

        // Statistiques mensuelles (janvier à décembre)
        $mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $statsMensuelles = [];
        foreach (range(1, 12) as $m) {
            $statsMensuelles[] = Demande::whereMonth('created_at', $m)->count();
        }

        return view('dashboards.admin', compact(
            'totalDemandes',
            'demandesValidees',
            'demandesRefusees',
            'demandesAttente',
            'admins',
            'responsables',
            'demandeurs',
            'agents',
            'mois',
            'statsMensuelles'
        ));
    }
    
}