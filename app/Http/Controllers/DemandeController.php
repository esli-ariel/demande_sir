<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DemandeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
          $user = Auth::user();
          $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->role === 'admin');
          $demandes = $isAdmin
            ? Demande::with('user')->latest()->get()
            : $user->demandes()->latest()->get();

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
        //
       $validated = $request->validate([
            'structure' => 'required|string|max:255',
            'unite' => 'nullable|string|max:255',
            'objet' => 'required|string|max:255',
            'motif' => 'nullable|string',
            'repere' => 'nullable|string|max:255',
            'situation_existante' => 'nullable|string',
            'situation_souhaitee' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        Demande::create($validated);

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
        return view('demandes.show', compact('demande'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demande $demande)
    {
        //
        $this->authorize('view', $demande);
        return view('demandes.show', compact('demande'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Demande $demande)
    {
        //
        $this->authorize('update', $demande);

        $validated = $request->validate([
            'objet' => 'required|string|max:255',
            'motif' => 'nullable|string',
            'situation_souhaitee' => 'nullable|string',
        ]);

        $demande->update($validated);

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
}
