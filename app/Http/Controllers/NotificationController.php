<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['page'=>'nullable|integer|min:1|max:100000', 'vue'=>'nullable|string|in:toutes,non_lues']);
        $scope = $filters['vue'] ?? 'toutes';
        $notifications = $request->user()->notifications()
            ->when($scope === 'non_lues', fn ($query) => $query->whereNull('read_at'))
            ->latest()->orderByDesc('id')->paginate(12)->withQueryString();
        $unread = $request->user()->unreadNotifications()->count();
        return view('admin.notifications', compact('notifications', 'unread', 'scope'));
    }

    public function summary(Request $request)
    {
        return response()->json(['unread'=>$request->user()->unreadNotifications()->count()])->header('Cache-Control', 'no-store');
    }

    public function read(Request $request, string $notification)
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();
        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at'=>now()]);
        return back()->with('success', 'Toutes vos notifications sont marquées comme lues.');
    }

    public function open(Request $request, string $notification)
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $demande = Demande::findOrFail($item->data['demande_id'] ?? null);
        Gate::authorize('view', $demande);
        $item->markAsRead();
        return redirect()->route('demandes.show', $demande);
    }
}
