<x-app-layout>
<div class="admin-dashboard">
    <header class="admin-page-heading"><h1>Notifications</h1><div class="admin-inline-actions"><span class="admin-count">{{ $unread }} non lue{{ $unread > 1 ? 's' : '' }}</span>@if($unread)<form method="POST" action="{{ route('notifications.read_all') }}">@csrf<button class="admin-button admin-button-secondary">Tout marquer comme lu</button></form>@endif</div></header>
    <section class="admin-panel">
        <div class="admin-panel-heading"><h2>{{ $scope === 'non_lues' ? 'Notifications non lues' : 'Toutes les notifications' }}</h2><div class="admin-inline-actions"><a href="{{ route('notifications.index') }}" @if($scope === 'toutes') aria-current="page" @endif>Toutes</a><a href="{{ route('notifications.index', ['vue'=>'non_lues']) }}" @if($scope === 'non_lues') aria-current="page" @endif>Non lues ({{ $unread }})</a></div></div>
        <div class="admin-notifications">@forelse($notifications as $notification)
        <article @class(['admin-notification', 'is-unread' => !$notification->read_at])>
            <div class="admin-notification-symbol" aria-hidden="true">{{ $notification->read_at ? '✓' : '●' }}</div>
            <div class="admin-notification-content">
                <div class="admin-notification-title"><h2>{{ $notification->data['message'] ?? ucfirst($notification->data['action'] ?? 'Nouvelle notification') }}</h2>@unless($notification->read_at)<span class="admin-badge admin-badge-validation">Non lue</span>@endunless</div>
                @if($notification->data['numero_dma'] ?? null)<span class="admin-reference">{{ $notification->data['numero_dma'] }}</span>@endif
                @if($notification->data['objet'] ?? null)<p>{{ $notification->data['objet'] }}</p>@endif
                @if(($notification->data['action'] ?? null) && ($notification->data['action'] ?? '') !== ($notification->data['message'] ?? ''))<p>{{ $notification->data['action'] }}</p>@endif
                @if($notification->data['commentaire'] ?? null)<p>{{ $notification->data['commentaire'] }}</p>@endif
                @if($notification->data['acteur'] ?? null)<p>Par {{ $notification->data['acteur'] }}</p>@endif
                <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('d/m/Y à H:i') }}</time>
                <div class="admin-inline-actions">@if($notification->data['demande_id'] ?? null)<form method="POST" action="{{ route('notifications.open', $notification->id) }}">@csrf<button>Consulter la demande ↗</button></form>@endif
                @unless($notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button>Marquer comme lue</button></form>@endunless</div>
            </div>
        </article>
        @empty<div class="admin-empty"><strong>{{ $scope === 'non_lues' ? 'Vous êtes à jour : aucune notification non lue.' : 'Aucune notification pour le moment.' }}</strong></div>@endforelse</div>
        <div class="admin-pagination">{{ $notifications->links('pagination.admin') }}</div>
    </section>
</div>
</x-app-layout>
