<?php

namespace App\Http\Controllers;

use App\Models\DemandePieceJointe;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DemandePieceJointeController extends Controller
{
    public function destroy(DemandePieceJointe $piece)
    {
        // Vérifie si l’utilisateur est autorisé à supprimer
        if (Auth::id() !== $piece->uploaded_by && !Auth::user()->hasRole('admin')) {
            abort(403, 'Action non autorisée.');
        }

        \Illuminate\Support\Facades\Gate::authorize('update', $piece->demande);

        // Supprimer le fichier physique
        if (Storage::disk('public')->exists($piece->chemin_fichier)) {
            Storage::disk('public')->delete($piece->chemin_fichier);
        }

        // Supprimer l’enregistrement en BD
        $piece->delete();

        return back()->with('success', '📁 Pièce jointe supprimée avec succès.');
    }
}
