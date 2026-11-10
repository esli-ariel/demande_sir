<?php

namespace App\Http\Controllers;

use App\Models\DemandeValidation;
use App\Support\StaffWorkspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function dashboard(Request $request)
    {
        $role = StaffWorkspace::role($request->user());
        abort_unless($role, 403);
        $workspace = StaffWorkspace::ROLES[$role];
        $query = StaffWorkspace::query($request->user());
        $total = (clone $query)->count();
        $pending = StaffWorkspace::pending(clone $query, $request->user())->count();
        $decisions = DemandeValidation::where('user_id', $request->user()->id)->where('role', $role);
        $processed = (clone $decisions)->distinct()->count('demande_id');
        $refused = (clone $decisions)->where('decision', 'refus')->distinct()->count('demande_id');
        if ($role === 'reception') {
            $processed = (clone $query)->where('statut','cloturee_receptionnee')->count();
            $refused = (clone $decisions)->distinct()->count('demande_id');
        }
        if ($role === 'chef_structure') {
            $processed = (clone $query)->where('statut','cloturee_receptionnee')->count();
            $refused = (clone $query)->whereIn('statut',\App\Support\DmaStatus::GROUPS['refusees'])->count();
        }
        if ($role === 'service_technique') {
            $processed = (clone $query)->where('statut','en_cours_traitement')->count();
            $refused = (clone $query)->whereIn('statut',['terminee_agent','cloturee','cloturee_receptionnee'])->count();
        }
        $demandes = StaffWorkspace::pending(clone $query, $request->user())->with('user')->oldest()->orderBy('id')->take(6)->get();

        return view('staff.dashboard', compact('role', 'workspace', 'total', 'pending', 'processed', 'refused', 'demandes'));
    }

    public function index(Request $request)
    {
        $role = StaffWorkspace::role($request->user());
        abort_unless($role, 403);
        $workspace = StaffWorkspace::ROLES[$role];
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'vue' => ['nullable', Rule::in(['a_traiter', 'mes_decisions', 'tous', 'cloturees', 'refusees'])]]);
        $search = trim($filters['q'] ?? '');
        $scope = $filters['vue'] ?? 'a_traiter';
        $query = StaffWorkspace::query($request->user())->with('user');
        if ($scope === 'cloturees') {
            $query->where('statut','cloturee_receptionnee');
        } elseif ($scope === 'refusees') {
            $query->whereIn('statut',\App\Support\DmaStatus::GROUPS['refusees']);
        } elseif ($scope === 'a_traiter') {
            StaffWorkspace::pending($query, $request->user());
        } elseif ($scope === 'mes_decisions' && ($workspace['readonly'] ?? false)) {
            $query->whereIn('statut',['cloturee_receptionnee', ...\App\Support\DmaStatus::GROUPS['refusees']]);
        } elseif ($scope === 'mes_decisions') {
            $query->whereIn('id', DemandeValidation::where('user_id', $request->user()->id)->where('role', $role)->select('demande_id'));
        }
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('numero_dma', 'like', '%'.$search.'%')->orWhere('objet_modif', 'like', '%'.$search.'%')->orWhere('nom', 'like', '%'.$search.'%'));
        }
        $demandes = $query->oldest()->orderBy('id')->paginate(10)->withQueryString();

        return view('staff.index', compact('role', 'workspace', 'search', 'scope', 'demandes'));
    }
}
