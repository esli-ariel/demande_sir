<x-app-layout>
<div class="admin-dashboard">
    <header class="admin-page-heading"><h1>Notifications</h1><span class="admin-count">{{ $unread }} non lue{{ $unread > 1 ? 's' : '' }}</span></header>
    <section class="admin-panel"><div class="admin-notifications">@forelse($notifications as $notification)
        <article @class(['admin-notification', 'is-unread' => !$notification->read_at])>
            <div class="admin-notification-symbol" aria-hidden="true">{{ $notification->read_at ? '✓' : '●' }}</div>
            <div class="admin-notification-content"><div class="admin-notification-title"><h2>{{ $notification->data['message'] ?? ucfirst($notification->data['action'] ?? 'Nouvelle notification') }}</h2>@unless($notification->read_at)<span class="admin-badge admin-badge-validation">Non lue</span>@endunless</div>@if($notification->data['objet'] ?? null)<p>{{ $notification->data['objet'] }}</p>@endif @if($notification->data['commentaire'] ?? null)<p>{{ $notification->data['commentaire'] }}</p>@endif<time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('d/m/Y à H:i') }}</time><div class="admin-inline-actions">@if($notification->data['demande_id'] ?? null)<a href="{{ route('demandes.show', $notification->data['demande_id']) }}">Consulter la demande ↗</a>@endif @unless($notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button>Marquer comme lue</button></form>@endunless</div></div>
        </article>
        @empty<div class="admin-empty"><strong>Aucune notification.</strong></div>@endforelse</div><div class="admin-pagination">{{ $notifications->links('pagination.admin') }}</div></section>
</div>
</x-app-layout>
