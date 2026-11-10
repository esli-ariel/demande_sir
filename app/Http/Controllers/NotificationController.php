<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(12);
        $unread = $request->user()->unreadNotifications()->count();

        return view($request->user()->hasAnyRole(['admin', 'demandeur']) ? 'admin.notifications' : 'notifications.index', compact('notifications', 'unread'));
    }

    public function read(Request $request, string $notification)
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back()->with('success', 'Notification marquée comme lue.');
    }
}
