<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\DemandePayloadRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
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
            $filters = $request->validate(['page'=>'nullable|integer|min:1|max:100000', 'search' => 'nullable|string|max:150', 'statut' => 'nullable|string|max:100']);
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
    public function store(DemandePayloadRequest $request)
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $request) {
            $demande = Demande::create(Arr::only($data, DemandePayloadRequest::FIELDS) + [
                'nom'=>$request->user()->name, 'user_id'=>$request->user()->id,
                'date_creation'=>now(), 'statut'=>'brouillon',
            ]);
            $demande->structuresSpecialisees()->attach($data['structures_specialisees'] ?? []);
            $this->saveFiles($demande, $data['pieces_jointes'] ?? []);
        });
        return redirect()->route('demandes.index')->with('success', 'Demande créée avec succès.');
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
    public function update(DemandePayloadRequest $request, Demande $demande)
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $demande) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            $this->authorize('update', $locked);
            $locked->update(array_replace(array_fill_keys(DemandePayloadRequest::FIELDS, null), Arr::only($data, DemandePayloadRequest::FIELDS)));
            if (filter_var($data['structures_included'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $locked->structuresSpecialisees()->sync($data['structures_specialisees'] ?? []);
            }
            $this->saveFiles($locked, $data['pieces_jointes'] ?? []);
        });
        return redirect()->route('demandes.index')->with('success', 'Demande mise à jour.');
    }

    private function saveFiles(Demande $demande, array $files): void
    {
        foreach ($files as $file) {
            DemandePieceJointe::create([
                'demande_id'=>$demande->id, 'chemin_fichier'=>$file->store('demandes', 'public'),
                'type_document'=>$file->extension(), 'uploaded_by'=>Auth::id(),
            ]);
        }
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
