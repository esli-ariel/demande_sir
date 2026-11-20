<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Support\DmaStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\DmaVisa;

class DemandeurController extends Controller
{
    public function dashboard(Request $request)
    {
        abort_unless($request->user()?->hasRole('demandeur'), 403);
        $query = $request->user()->demandes();
        $summary = DmaStatus::summary((clone $query)->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut')->all());
        $total = array_sum($summary);
        $receipts = (int) ((clone $query)->whereIn('statut', ['terminee_agent','cloturee'])->count());
        $active = $summary['validation'] + $summary['travaux'] + $summary['reception'];
        $demandes = (clone $query)->latest()->orderByDesc('id')->take(5)->get();

        return view('demandeur.dashboard', compact('summary', 'total', 'active', 'receipts', 'demandes'));
    }

    public function index(Request $request)
    {
        abort_unless($request->user()?->hasRole('demandeur'), 403);
        $filters = $request->validate(['page'=>'nullable|integer|min:1|max:100000', 'search' => 'nullable|string|max:150', 'statut' => 'nullable|string|max:100']);
        $search = trim($filters['search'] ?? '');
        $status = $filters['statut'] ?? '';
        $statuses = $request->user()->demandes()->distinct()->orderBy('statut')->pluck('statut');
        $demandes = $request->user()->demandes()
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q->where('numero_dma', 'like', '%'.$search.'%')->orWhere('objet_modif', 'like', '%'.$search.'%')->orWhere('motif', 'like', '%'.$search.'%')))
            ->when($status !== '', fn ($query) => $query->where('statut', $status))
            ->latest()->orderByDesc('id')->paginate(10)->withQueryString();

        return view('demandeur.index', compact('demandes', 'search', 'status', 'statuses'));
    }

    public function receptions(Request $request)
    {
        $request->validate(['page'=>'nullable|integer|min:1|max:100000']);
        abort_unless($request->user()?->hasRole('demandeur'), 403);
        $demandes = $request->user()->demandes()->whereIn('statut', ['terminee_agent','cloturee'])->latest()->orderByDesc('id')->paginate(10);

        return view('demandeur.receptions', compact('demandes'));
    }

    public function receptionner(Request $request, Demande $demande)
    {
        Gate::authorize('receptionner', $demande);
        $visa = app(DmaVisa::class);
        $data = $visa->validate($request,[
            'commentaire'=>['nullable','string','max:5000'],
        ],'confirmation_reception');
        $visa->run($request,function (User $actor) use ($demande,$data,$visa) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::forUser($actor)->authorize('receptionner',$locked);
            $signed = $visa->record($locked,$actor,'requester_receipt','cloturee_receptionnee',$data['commentaire'] ?? null);
            $locked->update(['statut'=>'cloturee_receptionnee','closed_at'=>$signed->signed_at]);
            app(\App\Services\DmaNotifications::class)->transition($locked, $actor, $data['commentaire'] ?? null);
        },'confirmation_reception');

        return redirect()->route('demandeur.receptions')->with('success','Réception confirmée. Votre DMA est clôturée et disponible dans les archives.');
    }

}
