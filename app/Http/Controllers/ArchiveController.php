<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchiveFiltersRequest;
use App\Models\Demande;
use App\Models\DemandeValidation;
use App\Models\User;
use App\Support\StaffWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

final class ArchiveController extends Controller
{
    private function accessible(User $user): Builder
    {
        $query = match (true) {
            $user->hasRole('admin') => Demande::query(),
            $user->hasRole('demandeur') => Demande::where('user_id', $user->id),
            default => StaffWorkspace::query($user),
        };

        return $query->where('statut', 'cloturee_receptionnee');
    }

    public function index(ArchiveFiltersRequest $request)
    {
        $filters = $request->validated();
        $query = $this->accessible($request->user());
        $years = (clone $query)->whereNotNull('closed_at')
            ->selectRaw('SUBSTR(closed_at, 1, 4) AS closure_year')->distinct()->orderByDesc('closure_year')->pluck('closure_year');
        $search = trim($filters['q'] ?? '');
        $unit = trim($filters['unite'] ?? '');
        if ($search !== '') {
            $query->where(fn (Builder $q) => $q->where('numero_dma','like','%'.$search.'%')
                ->orWhere('objet_modif','like','%'.$search.'%')->orWhere('nom','like','%'.$search.'%')
                ->orWhereHas('user', fn (Builder $users) => $users->where('name','like','%'.$search.'%')->orWhere('prenom','like','%'.$search.'%')));
        }
        if ($unit !== '') $query->where('unite_concernee', $unit);
        if (! empty($filters['annee'])) {
            $query->where('closed_at','>=',$filters['annee'].'-01-01 00:00:00')
                ->where('closed_at','<=',$filters['annee'].'-12-31 23:59:59');
        }
        if (! empty($filters['du'])) $query->where('closed_at','>=',$filters['du'].' 00:00:00');
        if (! empty($filters['au'])) $query->where('closed_at','<=',$filters['au'].' 23:59:59');
        $demandes = $query->with('user')->orderByDesc('closed_at')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('archives.index', compact('demandes','filters','years','search','unit'));
    }

    public function show(ArchiveFiltersRequest $request, Demande $demande)
    {
        $demande = $this->accessible($request->user())->whereKey($demande->id)
            ->with(['user','piecesJointes','structuresSpecialisees'])->firstOrFail();
        Gate::authorize('view', $demande);
        $decisions = DemandeValidation::with(['user','structure'])->where('demande_id',$demande->id)
            ->orderByDesc('date_validation')->orderByDesc('id')->get();

        return view('admin.demandes.show', [
            'demande'=>$demande, 'decisions'=>$decisions, 'fromArchives'=>true,
            'archiveFilters'=>$request->validated(),
        ]);
    }
}
