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
        $pending = (clone $query)->whereIn('statut', $workspace['pending'])->count();
        $decisions = DemandeValidation::where('user_id', $request->user()->id)->where('role', $role);
        $processed = (clone $decisions)->distinct()->count('demande_id');
        $refused = (clone $decisions)->where('decision', 'refus')->distinct()->count('demande_id');
        $demandes = (clone $query)->with('user')->whereIn('statut', $workspace['pending'])->oldest()->orderBy('id')->take(6)->get();

        return view('staff.dashboard', compact('role', 'workspace', 'total', 'pending', 'processed', 'refused', 'demandes'));
    }

    public function index(Request $request)
    {
        $role = StaffWorkspace::role($request->user());
        abort_unless($role, 403);
        $workspace = StaffWorkspace::ROLES[$role];
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'vue' => ['nullable', Rule::in(['a_traiter', 'mes_decisions', 'tous'])]]);
        $search = trim($filters['q'] ?? '');
        $scope = $filters['vue'] ?? 'a_traiter';
        $query = StaffWorkspace::query($request->user())->with('user');
        if ($scope === 'a_traiter') {
            $query->whereIn('statut', $workspace['pending']);
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
