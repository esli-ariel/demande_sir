<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use Illuminate\Support\Facades\Auth;
use App\Models\DemandePieceJointe;
use App\Models\User;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DemandeController extends Controller
{
   use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        $query = Demande::query();

    // 🔍 Filtre recherche
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('objet_modif', 'like', '%' . $request->search . '%')
              ->orWhere('motif', 'like', '%' . $request->search . '%')
              ->orWhere('nom', 'like', '%' . $request->search . '%');
        });
    }

    // 🎯 Filtre par statut
    if ($request->filled('statut')) {
        $query->where('statut', $request->statut);
    }
// 👤 Règles selon le rôle
    if (auth()->user()->hasRole('admin')) {
        $demandes = $query->latest()->get();
    } elseif (auth()->user()->hasRole('exploitant')) {
        $demandes = $query->where('statut', 'soumise')->latest()->get();
    } else {
        $demandes = $query->where('user_id', auth()->id())->latest()->get();
    }

    return view('demandes.index', compact('demandes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('demandes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'structure'            => 'required|string|max:255',
        'unite_concernee'      => 'required|string|max:255',
        'objet_modif'          => 'required|string|max:255',
        'motif'                => 'required|string',
        'repere'               => 'required|string|max:255',
        'fonction'             => 'required|string|max:255',
        'situation_existante'  => 'required|string',
        'situation_souhaitee'  => 'required|string',
        'pieces_jointes.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
    ]);

    // Remplissage automatique
    $validated['nom'] = Auth::user()->name;
    $validated['user_id'] = Auth::id();
    $validated['date_creation'] = now();
    $validated['statut'] = 'brouillon'; // par défaut

    $demande = Demande::create($validated);

        // 🔹 Enregistrement des pièces jointes
    if ($request->hasFile('pieces_jointes')) {
        foreach ($request->file('pieces_jointes') as $fichier) {
            $chemin = $fichier->store('demandes', 'public');

            DemandePieceJointe::create([
                'demande_id' => $demande->id,
                'chemin_fichier' => $chemin,
                'type_document' => $fichier->extension(),
                'uploaded_by' => Auth::id(),
            ]);
            
    return redirect()->route('demandes.index')
        ->with('success', 'Demande créée avec succès.');
}
    }
}
    /**
     * Display the specified resource.
     */
    public function show(Demande $demande)
    {
        //
        $this->authorize('view', $demande);
        return view('demandes.show', compact('demande'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demande $demande)
    {
        //
        $this->authorize('view', $demande);
        return view('demandes.edit', compact('demande'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Demande $demande)
    {
        //
        $this->authorize('update', $demande);
         // 🔒 Vérification : seul le créateur ou un admin peut modifier
    if (Auth::id() !== $demande->user_id && !Auth::user()->hasRole('admin')) {
        abort(403, 'Accès non autorisé.');
    }
        $validated = $request->validate([
        'structure' => 'required|string|max:255',
        'unite_concernee' => 'nullable|string|max:255',
        'objet_modif' => 'required|string|max:255',
        'motif' => 'nullable|string',
        'repere' => 'nullable|string|max:255',
        'fonction' => 'nullable|string|max:255',
        'situation_existante' => 'nullable|string',
        'situation_souhaitee' => 'nullable|string',
        'pieces_jointes.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        ]);

        $demande->update([
        'structure' => $validated['structure'],
        'unite_concernee' => $validated['unite_concernee'] ?? null,
        'objet_modif' => $validated['objet_modif'],
        'motif' => $validated['motif'] ?? null,
        'repere' => $validated['repere'] ?? null,
        'fonction' => $validated['fonction'] ?? null,
        'situation_existante' => $validated['situation_existante'] ?? null,
        'situation_souhaitee' => $validated['situation_souhaitee'] ?? null,
        ]);

         // 📎 Ajout de nouvelles pièces jointes si présentes
    if ($request->hasFile('pieces_jointes')) {
        foreach ($request->file('pieces_jointes') as $fichier) {
            $chemin = $fichier->store('demandes', 'public');

            DemandePieceJointe::create([
                'demande_id' => $demande->id,
                'chemin_fichier' => $chemin,
                'type_document' => $fichier->getClientOriginalExtension(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }
        return redirect()->route('demandes.index')
            ->with('success', 'Demande mise à jour.');
        
           //  return redirect()->route('demandes.show', $demande)
        //->with('success', 'Demande mise à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Demande $demande)
    {
        //
        $this->authorize('delete', $demande);
        $demande->delete();

        return redirect()->route('demandes.index')
            ->with('success', 'Demande supprimée.');
    }
    public function submit(Demande $demande)
{
    if ($demande->user_id !== auth()->id()) {
        abort(403, 'Accès interdit.');
    }

    $demande->update([
        'statut' => 'soumise'
    ]);

    return redirect()->route('demandes.index')
        ->with('success', 'Demande soumise au responsable.');
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

public function indexExploitation()
{
    // On filtre les demandes "soumise" ou "en attente exploitation"
    $demandes = Demande::where('statut', 'soumise')
        ->orWhere('statut', 'en_cours_traitement')
        ->latest()
        ->get();

    return view('demandes.index_exploitation', compact('demandes'));
}
public function indexDTS()
{
    $demandes = Demande::where('statut', 'validee_exploitation')->latest()->get();
    return view('demandes.index_dts', compact('demandes'));
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


////////////////////////////////////////////////////////////
// 🧩 STRUCTURE SPÉCIALISÉE
////////////////////////////////////////////////////////////
public function indexStructure()
{
    $demandes = Demande::where('statut', 'validee_dts')->get();
    return view('demandes.index_structure', compact('demandes'));
}

public function validerStructure(Demande $demande)
{
    $demande->update(['statut' => 'validee_structure_specialisee']);
    return back()->with('success', '✅ Demande validée par la structure spécialisée.');
}

public function rejeterStructure(Demande $demande)
{
    $demande->update(['statut' => 'refusee_structure_specialisee']);
    return back()->with('success', '❌ Demande refusée par la structure spécialisée.');
}


////////////////////////////////////////////////////////////
// 🧩 CONTRÔLE AVANCÉ
////////////////////////////////////////////////////////////
public function indexControle()
{
    $demandes = Demande::where('statut', 'validee_structure_specialisee')->get();
    return view('demandes.index_controle', compact('demandes'));
}

public function validerControle(Request $request, Demande $demande)
{
    $request->validate([
        'numero_dma' => 'required|string|max:50',
    ]);

    $demande->update([
        'statut' => 'validee_controle_avancee',
        'numero_dma' => $request->numero_dma,
    ]);

    return back()->with('success', '✅ Demande validée par le contrôle avancé.');
}

public function rejeterControle(Demande $demande)
{
    $demande->update(['statut' => 'refusee_controle_avancee']);
    return back()->with('success', '❌ Demande refusée par le contrôle avancé.');
}


////////////////////////////////////////////////////////////
// SERVICE TECHNIQUE
////////////////////////////////////////////////////////////
public function indexServiceTechnique()
{
    $demandes = Demande::where('statut', 'validee_controle_avancee')->get();
    return view('demandes.index_service', compact('demandes'));
}

public function traiter(Request $request, Demande $demande)
{
    $request->validate([
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
        'travaux_realises' => 'required|string',
    ]);

    $demande->update([
        'date_debut' => $request->date_debut,
        'date_fin' => $request->date_fin,
        'travaux_realises' => $request->travaux_realises,
        'statut' => 'terminee_agent',
    ]);

    return back()->with('success', 'modification effectuée et enregistrer .');
}

}
