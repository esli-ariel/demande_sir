<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


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

           
          // Statistiques principales
    $totalDemandes = \App\Models\Demande::count();
    $demandesValidees = \App\Models\Demande::where('statut', 'cloturee_receptionnee')->count();
    $demandesRefusees = \App\Models\Demande::wherein('statut', ['refusee','refusee_exploitation','refusee_dts','refusee_structure_specialisee','refusee_controle_avancee','terminee_agent'])->count();
    $demandesAttente = \App\Models\Demande::wherein('statut', ['en_attente','soumise','validee_exploitation','validee_dts','validee_structure_specialisee','validee_controle_avancee'])->count();

    // Statistiques utilisateurs
    $admins = \App\Models\User::role('admin')->count();
    $responsables = \App\Models\User::role('controle_avancee')->count();
    $demandeurs = \App\Models\User::role('demandeur')->count();
    $agents = \App\Models\User::role('service_technique')->count();

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
        'data',
        'evolutionMensuelle'
    ));


            
        } 
        
       // elseif ($user->hasRole('demandeur')) {
        //    $demandes = $user->demandes()->latest()->get();
         //   return view('dashboards.demandeur', compact('demandes'));
       // }

        //$user->assignRole('demandeur');
   // return view('dashboards.demandeur');

        // Cas DEMANDEUR → il ne voit que ses propres demandes
        if ($user->hasRole('demandeur')) {

            $user = Auth::user();

        // Si l'utilisateur est un demandeur → il ne voit que ses demandes
        
        $demandes = Demande::where('user_id', $user->id)->get();

        // Calcul des statistiques
        $totalDemandes = $demandes->count();
        $demandesValidees = $demandes->wherein('statut', ['validee_controle_avancee','terminee_agent','cloturee_receptionnee'])->count();
        $demandesRejetees = $demandes->wherein('statut', ['refusee_dts','refusee_controle_avancee', 'refusee_exploitation', 'rejetee_structure_specialisee','refusee_controle_avancee',])->count();
        $demandesEnCours = $demandes->wherein('statut', ['soumis','validee_exploitation','validee_controle_avancee','validee_dts','validee_structure_specialisee',])->count();

        // Taux de validation (évite la division par 0)
        $tauxValidation = $totalDemandes > 0 ? round(($demandesValidees / $totalDemandes) * 100, 2) : 0;

        // Répartition par statut pour les graphiques
        $repartition = [
            'validée' => $demandesValidees,
            'rejetée' => $demandesRejetees,
            'en cours' => $demandesEnCours,
        ];

        // Statistiques par mois (si tu veux afficher un graphique mensuel)
        $statsMensuelles = Demande::selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->when($user->role === 'demandeur', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        $labelsMois = $statsMensuelles->pluck('mois')->map(function ($m) {
            return date('F', mktime(0, 0, 0, $m, 1));
        });

        $valeursMois = $statsMensuelles->pluck('total');

        return view('dashboards.demandeur', compact(
            'totalDemandes',
            'demandesValidees',
            'demandesRejetees',
            'demandesEnCours',
            'tauxValidation',
            'repartition',
            'labelsMois',
            'valeursMois',
            'demandes'
        ));
    
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

            return view('dashboards.service', compact('demandes','stats'));
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


        // Si l'utilisateur est un demandeur → il ne voit que ses demandes
            $demandes = Demande::where('user_id', $user->id)->get();
        // Calcul des statistiques
        $totalDemandes = $demandes->count();
        $demandesValidees = $demandes->wherein('statut', ['validee_controle_avancee','terminee_agent'])->count();
        $demandesRejetees = $demandes->wherein('statut', ['refusee_controle_avancee', 'refusee_exploitation', 'rejetee_structure_specialisee'])->count();
        $demandesEnCours = $demandes->wherein('statut', ['soumis','',''])->count();

        // Taux de validation (évite la division par 0)
        $tauxValidation = $totalDemandes > 0 ? round(($demandesValidees / $totalDemandes) * 100, 2) : 0;

        // Répartition par statut pour les graphiques
        $repartition = [
            'validée' => $demandesValidees,
            'rejetée' => $demandesRejetees,
            'en cours' => $demandesEnCours,
        ];

        // Statistiques par mois (si tu veux afficher un graphique mensuel)
        $statsMensuelles = Demande::selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->when($user->role === 'demandeur', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        $labelsMois = $statsMensuelles->pluck('mois')->map(function ($m) {
            return date('F', mktime(0, 0, 0, $m, 1));
        });

        $valeursMois = $statsMensuelles->pluck('total');

        return view('dashboards.demandeur', compact(
            'totalDemandes',
            'demandesValidees',
            'demandesRejetees',
            'demandesEnCours',
            'tauxValidation',
            'repartition',
            'labelsMois',
            'valeursMois',
            'demandes'
        ));
    }

public function admin()
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
    $responsables = \App\Models\User::role('responsable')->count();
    $demandeurs = \App\Models\User::role('demandeur')->count();
    $agents = \App\Models\User::role('agent')->count();

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

    return view('dashboards.service', compact('stats', 'demandes'));
}


}