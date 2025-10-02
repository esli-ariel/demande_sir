<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
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
        elseif ($user->hasRole('responsable_S')) {
            $demandes = Demande::where('statut', 'en_attente_validation')->get();
            return view('dashboards.responsable', compact('demandes'));
        } 
        elseif ($user->hasRole('service_technique')) {
            $demandes = Demande::where('statut', 'validee_responsable')->get();
            return view('dashboards.service', compact('demandes'));
        } 
        elseif ($user->hasRole('demandeur')) {
            $demandes = $user->demandes()->latest()->get();
            return view('dashboards.demandeur', compact('demandes'));
        }

        $user->assignRole('demandeur');
    return view('dashboards.demandeur');
    }
}
