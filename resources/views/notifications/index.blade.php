<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">📬 Mes notifications</h2>
    </x-slot>

    <div class="max-w-4xl p-6 mx-auto mt-6 bg-white rounded-lg shadow">
        @forelse($notifications as $notification)
            <div class="p-4 mb-3 border rounded hover:bg-gray-50">
                <p>{{ $notification->data['message'] ?? 'Nouvelle notification' }}</p>
                <p class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="text-gray-500">Aucune notification disponible.</p>
        @endforelse
    </div>
</x-app-layout>
