<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\User;
use App\Support\DmaStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'etat' => ['nullable', Rule::in(array_keys(DmaStatus::GROUP_LABELS))],
        ]);
        $search = trim($filters['q'] ?? '');
        $category = $filters['etat'] ?? '';
        $statusCounts = Demande::query()->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut')->all();
        $summary = DmaStatus::summary($statusCounts);
        $total = array_sum($summary);
        $active = $summary['validation'] + $summary['travaux'] + $summary['reception'];

        $query = Demande::with('user');
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('numero_dma', 'like', '%'.$search.'%')
                    ->orWhere('objet_modif', 'like', '%'.$search.'%')
                    ->orWhere('nom', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$search.'%')->orWhere('prenom', 'like', '%'.$search.'%'));
            });
        }
        if ($category === 'autres') {
            $query->whereNotIn('statut', array_merge(...array_values(DmaStatus::GROUPS)));
        } elseif ($category !== '') {
            $query->whereIn('statut', DmaStatus::GROUPS[$category]);
        }
        $demandes = $query->latest()->orderByDesc('id')->paginate(8)->withQueryString();

        $year = now()->year;
        $monthly = Demande::query()->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as mois, COUNT(*) as total')->groupBy('mois')->pluck('total', 'mois');
        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $monthlyCounts = collect(range(1, 12))->map(fn ($month) => (int) ($monthly[$month] ?? 0));
        $usersCount = User::count();

        return view('dashboards.admin', compact('summary', 'total', 'active', 'demandes', 'search', 'category', 'year', 'months', 'monthlyCounts', 'usersCount'));
    }
}
