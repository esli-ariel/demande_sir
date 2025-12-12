<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;

class ValidationController extends Controller
{
    // Liste des demandes pour un responsable
    public function index()
    {
        $demandes = Demande::where('statut', 'en_attente_validation')->get();
        return view('responsable.validations', compact('demandes'));
    }

    // Validation par un responsable
    public function valider(Demande $demande)
    {
        $demande->update(['statut' => 'validee_responsable']);
        return redirect()->route('dashboard')->with('success', 'Demande validée.');
    }

    // Rejet par un responsable
    public function rejeter(Demande $demande)
    {
        $demande->update(['statut' => 'rejete']);
        return redirect()->route('dashboard')->with('error', 'Demande rejetée.');
    }

    // Liste des demandes pour le service technique
    public function executions()
    {
        $demandes = Demande::where('statut', 'validee_responsable')->get();
        return view('service.executions', compact('demandes'));
    }

    // Exécution par le service technique
    public function traiter(Demande $demande)
    {
        $demande->update(['statut' => 'terminee_agent']);
        return redirect()->route('dashboard')->with('success', 'Demande exécutée.');
    }
    // Clôture par le service technique
    public function cloturer(Demande $demande)
    {
        $demande->update(['statut' => 'cloturee']);

        return back()->with('success', 'Demande clôturée.');
    }
}
