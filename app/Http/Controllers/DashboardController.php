<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
            $demandes = Demande::wherein('statut',['soumise' , 'validee_exploitation' , 'refusee_exploitation'] )->get();
             // Calcul des statistiques pour les cartes
    $stats = [
        'total'      => $demandes->count(),
        'en_attente' => $demandes->where('statut', 'soumise')->count(),
        'traitees'   => $demandes->where('statut', 'validee_exploitation')->count(),
        'rejetees'   => $demandes->where('statut', 'refusee_exploitation')->count(),
    ];

    return view('dashboards.exploitation', compact('demandes', 'stats'));
            //return view('dashboards.exploitation', compact('demandes'));
            
        }

        // Cas DTS → il voit les demandes validées exploitation
        if ($user->hasRole('dts')) {
            $demandes = Demande::wherein('statut',[
        'validee_exploitation', 'validee_dts', 'refusee_dts'
    ])->latest()
    ->take(10)
    ->get();

    // Statistiques pour la Direction Technique (DTS)
    $stats = [
        'total'      => \App\Models\Demande::count(),
        'en_attente' => \App\Models\Demande::where('statut', 'validee_exploitation')->count(),
        'traitees'   => \App\Models\Demande::where('statut', 'validee_dts')->count(),
        'rejetees'   => \App\Models\Demande::where('statut', 'refusee_dts')->count(),
    ];
            return view('dashboards.dts', compact('demandes', 'stats'));
        }

        // Cas STRUCTURE SPÉCIALISÉE → il voit les demandes validées DTS
        if ($user->hasRole('structure_specialisee')) {
            $demandes = Demande::wherein('statut', ['validee_dts','validee_structure_specialisee','refusee_structure_specialisee'])->get();
            
            // Statistiques globales
    $stats = [
        'total'      => \App\Models\Demande::count(),
        'en_attente' => \App\Models\Demande::where('statut', 'validee_dts')->count(),
        'traitees'   => \App\Models\Demande::where('statut', 'validee_structure_specialisee')->count(),
        'rejetees'   => \App\Models\Demande::where('statut', 'refusee_structure_specialisee')->count(),
    ];

    

    // Dernières demandes pour affichage dans le tableau
    $demandes = \App\Models\Demande::latest()->take(10)->get();

    return view('dashboards.structures', compact('stats', 'demandes'));

            //return view('dashboards.structures', compact('demandes'));
        }

        // Cas CONTRÔLE AVANCÉ → il voit les demandes validées par toutes les structures
        if ($user->hasRole('controle_avancee')) {
            $demandes = Demande::wherein('statut',[
        'validee_structure_specialisee',
        'validee_controle_avancee',
        'refusee_controle_avancee',
        'terminee_agent',
        'cloturee_receptionnee'])->get();


        $stats = [
        'total'               => \App\Models\Demande::whereIn('statut', [
            'validee_structure_specialisee',
            'validee_controle',
            'validee_controle_avancee',
            'refusee_controle_avancee',
            'terminee_agent',
            'cloturee_receptionnee'
        ])->count(),

        'en_cours_validation' => \App\Models\Demande::whereIn('statut', [
            'validee_structure_specialisee',
            'validee_controle'
        ])->count(),

        'validees'            => \App\Models\Demande::where('statut', 'validee_controle_avancee')->count(),

        'rejetees'            => \App\Models\Demande::where('statut', 'refusee_controle_avancee')->count(),

        'cloturees'           => \App\Models\Demande::where('statut', 'cloturee_receptionnee')->count(),

        'en_cours_cloture'    => \App\Models\Demande::where('statut', 'terminee_agent')->count(),
    ];

    $demandes = \App\Models\Demande::whereIn('statut', [
        'validee_structure_specialisee',
        'validee_controle',
        'validee_controle_avancee',
        'refusee_controle_avancee',
        'terminee_agent',
        'cloturee_receptionnee'
    ])->latest()->take(10)->get();
            
            return view('dashboards.controle', compact('demandes','stats'));
        }

        // Cas SERVICE TECHNIQUE → il voit les DMA validées par contrôle avancé
        if ($user->hasRole('service_technique')) {
            $demandes = Demande::wherein('statut', ['validee_controle_avancee',
        'en_cours_traitement',
        'terminee_agent'])->get();



        
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

        // debug rapide : écrire dans le log
    Log::info('DashboardController@admin called', ['user_id' => auth()->id()]);
        // ⚠️ Sécurité : éviter erreurs si pas encore de données
        $totalDemandes = Demande::count() ?? 0;

        $demandesValidees = Demande::where('statut', 'LIKE', '%validee%')->count() ?? 0;
        $demandesRefusees = Demande::where('statut', 'LIKE', '%refusee%')->count() ?? 0;
        $demandesAttente = Demande::whereIn('statut', ['soumise', 'brouillon'])->count() ?? 0;

        // Comptage des rôles (via Spatie)
        $admins = User::role('admin')->count() ?? 0;
        $responsables = User::role('structure_specialisee')->count() ?? 0;
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
    
    public function exploitation()
{
    $demandes = \App\Models\Demande::whereIn('statut', ['soumise', 'validee_exploitation', 'refusee_exploitation'])
        ->latest()
        ->take(10)
        ->get();

    $stats = [
        'total'     => $demandes->count(),
        'en_attente'=> $demandes->where('statut', 'soumise')->count(),
        'traitees'  => $demandes->where('statut', 'validee_exploitation')->count(),
        'rejetees'  => $demandes->where('statut', 'refusee_exploitation')->count(),
    ];

    return view('dashboards.exploitation', compact('stats', 'demandes'));
}

public function structure()
{
    // Statistiques globales
    $stats = [
        'total'      => \App\Models\Demande::count(),
        'en_attente' => \App\Models\Demande::where('statut', 'validee_exploitation')->count(),
        'traitees'   => \App\Models\Demande::where('statut', 'validee_structure_specialisee')->count(),
        'rejetees'   => \App\Models\Demande::where('statut', 'refusee_structure_specialisee')->count(),
    ];

    // Graphique : nombre de demandes par structure
    $chartData = \App\Models\StructureSpecialisee::withCount('demandes')
        ->get(['nom', 'demandes_count'])
        ->map(fn($s) => [
            'label' => $s->nom,
            'count' => $s->demandes_count,
        ]);

    // Dernières demandes pour affichage dans le tableau
    $demandes = \App\Models\Demande::latest()->take(10)->get();

    return view('dashboards.structure', compact('stats', 'chartData', 'demandes'));
}

public function dts()
{
    // Statistiques pour la Direction Technique (DTS)
    $stats = [
        'total'      => \App\Models\Demande::count(),
        'en_attente' => \App\Models\Demande::where('statut', 'validee_exploitation')->count(),
        'traitees'   => \App\Models\Demande::where('statut', 'validee_dts')->count(),
        'rejetees'   => \App\Models\Demande::where('statut', 'refusee_dts')->count(),
    ];

    // Liste des demandes pertinentes pour le DTS
    $demandes = \App\Models\Demande::whereIn('statut', [
        'validee_exploitation', 'validee_dts', 'refusee_dts'
    ])
    ->latest()
    ->take(10)
    ->get();

    return view('dashboards.dts', compact('stats', 'demandes'));
}

public function controleAvance()
{
    $stats = [
        'total'               => \App\Models\Demande::whereIn('statut', [
            'validee_structure_specialisee',
            'validee_controle',
            'validee_controle_avancee',
            'refusee_controle_avancee',
            'terminee_agent',
            'cloturee_receptionnee'
        ])->count(),

        'en_cours_validation' => \App\Models\Demande::whereIn('statut', [
            'validee_structure_specialisee',
            'validee_controle'
        ])->count(),

        'validees'            => \App\Models\Demande::where('statut', 'validee_controle_avancee')->count(),

        'rejetees'            => \App\Models\Demande::where('statut', 'refusee_controle_avancee')->count(),

        'cloturees'           => \App\Models\Demande::where('statut', 'cloturee_receptionnee')->count(),

        'en_cours_cloture'    => \App\Models\Demande::where('statut', 'terminee_agent')->count(),
    ];

    $demandes = \App\Models\Demande::whereIn('statut', [
        'validee_structure_specialisee',
        'validee_controle',
        'validee_controle_avancee',
        'refusee_controle_avancee',
        'terminee_agent',
        'cloturee_receptionnee'
    ])->latest()->take(10)->get();

    return view('dashboards.controle', compact('stats', 'demandes'));
}

public function serviceTechnique()
{
    $stats = [
        'total'      => \App\Models\Demande::whereIn('statut', [
            'validee_controle_avancee',
            'en_cours_traitement',
            'terminee_agent'
        ])->count(),

        'en_cours'   => \App\Models\Demande::where('statut', 'en_cours_traitement')->count(),

        'validees'   => \App\Models\Demande::where('statut', 'validee_controle_avancee')->count(),

        'terminees'  => \App\Models\Demande::where('statut', 'terminee_agent')->count(),
    ];

    $demandes = \App\Models\Demande::whereIn('statut', [
        'validee_controle_avancee',
        'en_cours_traitement',
        'terminee_agent'
    ])->latest()->get();

    return view('dashboards.service_technique', compact('stats', 'demandes'));
}


}