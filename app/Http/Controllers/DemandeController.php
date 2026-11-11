<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use Illuminate\Support\Facades\Auth;
use App\Models\DemandePieceJointe;
use App\Models\StructureSpecialisee;
use Illuminate\Support\Facades\Gate;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DemandeController extends Controller
{
   use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole('admin')) {
            $filters = $request->validate(['search' => 'nullable|string|max:150', 'statut' => 'nullable|string|max:100']);
            $search = trim($filters['search'] ?? '');
            $status = $filters['statut'] ?? '';
            $statuses = Demande::query()->distinct()->orderBy('statut')->pluck('statut');
            $demandes = Demande::with('user')
                ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q->where('numero_dma', 'like', '%'.$search.'%')->orWhere('objet_modif', 'like', '%'.$search.'%')->orWhere('nom', 'like', '%'.$search.'%')))
                ->when($status !== '', fn ($query) => $query->where('statut', $status))
                ->latest()->orderByDesc('id')->paginate(10)->withQueryString();

            return view('admin.demandes.index', compact('demandes', 'search', 'status', 'statuses'));
        }

        if (\App\Support\StaffWorkspace::role($request->user())) {
            return app(StaffController::class)->index($request);
        }

        if ($request->user()->hasRole('demandeur')) {
            return app(DemandeurController::class)->index($request);
        }

        abort(403, 'Aucun espace de demandes disponible pour votre rôle.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Demande::class);
        if (auth()->user()->hasAnyRole(\App\Support\StaffWorkspace::modernRoles())) {
            return view('admin.demandes.form', ['structures' => StructureSpecialisee::orderBy('nom')->get()]);
        }
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
    $this->authorize('create', Demande::class);
    if ($request->user()->hasAnyRole(\App\Support\StaffWorkspace::modernRoles())) {
        $request->validate([
            'structures_specialisees' => 'nullable|array',
            'structures_specialisees.*' => 'integer|distinct|exists:structures_specialisees,id',
        ]);
    }
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
            
        }
    }

    return redirect()->route('demandes.index')
        ->with('success', 'Demande créée avec succès.');
}
    /**
     * Display the specified resource.
     */
    public function show(Demande $demande)
    {
        //
        $this->authorize('view', $demande);
        if (auth()->user()->hasAnyRole(\App\Support\StaffWorkspace::modernRoles())) {
            $decisions = \App\Models\DemandeValidation::with(['user', 'structure'])->where('demande_id', $demande->id)->orderByDesc('date_validation')->orderByDesc('id')->get();
            return view('admin.demandes.show', compact('demande', 'decisions'));
        }
        return view('demandes.show', compact('demande'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demande $demande)
    {
        //
        $this->authorize('view', $demande);
        if (auth()->user()->hasAnyRole(\App\Support\StaffWorkspace::modernRoles())) {
            $this->authorize('update', $demande);
            return view('admin.demandes.form', ['demande' => $demande, 'structures' => StructureSpecialisee::orderBy('nom')->get()]);
        }
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
        $request->validate([
            'structures_included' => 'nullable|boolean',
            'structures_specialisees' => 'nullable|array',
            'structures_specialisees.*' => 'integer|distinct|exists:structures_specialisees,id',
        ]);
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
        if ($request->boolean('structures_included')) {
            $demande->structuresSpecialisees()->sync($request->input('structures_specialisees', []));
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
    \Illuminate\Support\Facades\DB::transaction(function () use ($demande) {
        $locked = Demande::lockForUpdate()->findOrFail($demande->id);
        $this->authorize('submit', $locked);
        validator($locked->only(['structure', 'unite_concernee', 'objet_modif', 'motif', 'repere', 'fonction', 'situation_existante', 'situation_souhaitee']), [
            'structure' => 'required|string|max:255', 'unite_concernee' => 'required|string|max:255',
            'objet_modif' => 'required|string|max:255', 'motif' => 'required|string',
            'repere' => 'required|string|max:255', 'fonction' => 'required|string|max:255',
            'situation_existante' => 'required|string', 'situation_souhaitee' => 'required|string',
        ])->validate();
        $locked->update(['statut' => 'soumise']);
        app(\App\Services\DmaNotifications::class)->transition($locked, auth()->user());
    });

    return redirect()->route('demandes.show', $demande)->with('success', 'Demande envoyée à l’exploitation.');
}
}
