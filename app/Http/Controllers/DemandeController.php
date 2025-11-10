<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DemandePieceJointe;
use App\Models\User;
use App\Models\StructureSpecialisee;
use Illuminate\Support\Facades\Gate;
use App\Notifications\DemandeStatutChangeNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DemandeClotureeNotification;


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

    if (Auth::user()->hasRole('structure_specialisee')) {
    $structure = StructureSpecialisee::where('user_id', Auth::id())->first();

    $demandes = $structure
        ? $structure->demandes()->where('statut', 'validee_dts')->get()
        : collect(); // aucune structure associée
} else {
    $demandes = Demande::all();
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
        // On récupère uniquement les utilisateurs ayant le rôle 'structure_specialisee'
    $structures = User::role('structure_specialisee')->get();

    return view('demandes.create', compact('structures'));
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

     // 🔗 Attacher les structures sélectionnées
    if ($request->has('structures_specialisees')) {
        $demande->structuresSpecialisees()->attach($request->input('structures_specialisees'));
    }

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
        // debug rapide : écrire dans le log
    Log::info('DashboardController@admin called', ['user_id' => auth()->id()]);
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

public function validerOuRejeterStructure(Request $request, Demande $demande)
{
    // Vérifie que l'utilisateur est bien une structure spécialisée
    Gate::authorize('validerStructureSpecialisee', $demande);

    $validated = $request->validate([
        'decision' => 'required|in:accord,refus',
        'visa' => 'required|string|max:255',
        'commentaire' => 'nullable|string',
    ]);

    // 1️⃣ Enregistrer la décision individuelle de cette structure
    $demande->validations()->create([
        'user_id' => Auth::id(),
        'role' => 'structure_specialisee',
        'decision' => $validated['decision'],
        'visa' => $validated['visa'],
        'commentaire' => $validated['commentaire'],
        'date_validation' => now(),
    ]);

    // 2️⃣ Vérifier si une structure a refusé
    $refus = $demande->validations()
        ->where('role', 'structure_specialisee')
        ->where('decision', 'refus')
        ->exists();

    if ($refus) {
        $demande->update(['statut' => 'refusee_structure_specialisee']);
        return back()->with('error', '❌ Une structure spécialisée a refusé — la demande est rejetée.');
    }

    // 3️⃣ Vérifier si toutes les structures ont validé
    $total = $demande->structuresSpecialisees()->count();
    $validees = $demande->validations()
        ->where('role', 'structure_specialisee')
        ->where('decision', 'accord')
        ->count();

    if ($validees === $total && $total > 0) {
        $demande->update(['statut' => 'validee_structure_specialisee']);
        return back()->with('success', '✅ Toutes les structures spécialisées ont validé — demande envoyée au contrôle avancé.');
    }

    // 4️⃣ Sinon, on attend encore des validations
    return back()->with('info', 'Validation enregistrée — en attente des autres structures.');
}



////////////////////////////////////////////////////////////
// 🧩 CONTRÔLE AVANCÉ
////////////////////////////////////////////////////////////
public function indexControle()
{
    $demandes = Demande::where('statut', 'validee_structure_specialisee')->get();
    return view('demandes.index_controle', compact('demandes'));
}

public function indexCloture()
{
    $demandes = Demande::where('statut', 'terminee_agent')->get();
    return view('demandes.cloture', compact('demandes'));
}

public function validerControle(Request $request, Demande $demande)
{
    $request->validate([
        'numero_dma' => 'required|string|max:50',
        'visa' => 'required|string',
        'commentaire' => 'nullable|string',
    ]);

    $demande->update([
        'statut' => 'validee_controle_avancee',
        'numero_dma' => $request->numero_dma,
        'visa_controle_avance' => $request->visa,
        'commentaire_controle_avance' => $request->commentaire,
    ]);

   // Notification interne
    $demande->user->notify(new DemandeStatutChangeNotification(
        $demande,
        'validée par le controle avancé,bientot prise en charge par le service technique',
        $request->commentaire
    ));
    
    return back()->with('success', '✅ Demande validée par le contrôle avancé.');
}

public function rejeterControle(Demande $demande, Request $request)
{

     $request->validate([
        'visa' => 'required|string',
        'commentaire' => 'required|string',
    ]);

    $demande->update([
        'statut' => 'refusee_controle_avancee',
        'visa_controle_avance' => $request->visa,
        'commentaire_controle_avance' => $request->commentaire,
    ]);

      // Notification interne
    $demande->user->notify(new DemandeStatutChangeNotification(
        $demande,
        'rejetée par le contrôle avancé',
        $request->commentaire
    ));

    return back()->with('success', '❌ Demande refusée par le contrôle avancé.');
}


public function cloturer(Request $request, Demande $demande)
{
    $request->validate([
        'analyse' => 'required|string|max:500',
        'visa' => 'nullable|string|max:50',
    ]);

    // Mise à jour du statut
    $demande->update([
        'statut' => 'cloturee_receptionnee',
        'analyse_finale' => $request->analyse,
        'visa_controle_avance' => $request->visa,
        'date_cloture' => now(),
    ]);

    // Notification in-app pour le demandeur
    $demande->user->notify(new DemandeClotureeNotification($demande));

    // (Optionnel) archivage dans une table séparée ou colonne "archivee"
    $demande->update(['archivee' => true]);

    return back()->with('success', 'Demande clôturée et notifiée avec succès.');
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
