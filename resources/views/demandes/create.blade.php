<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Nouvelle Demande</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        {{-- 🎨 Image de fond filigrane --}}
        <div class="absolute inset-0 z-0 opacity-10"
            style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 200%;">
        </div>

        {{-- ✅ Contenu au-dessus du fond --}}
        <div class="relative z-10 px-6 py-10 sm:px-12 lg:px-24">
            {{-- 🔔 Affichage des erreurs --}}
            @if ($errors->any())
            <div class="p-4 mb-6 text-red-800 bg-red-100 border border-red-300 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- 🧾 Formulaire de création --}}
            <form action="{{ route('demandes.store') }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-4 bg-white rounded-lg shadow-md">
                @csrf

                <div>
                    <label class="font-semibold">Structure</label>
                    <input type="text" name="structure" placeholder="Structure"
                        class="w-full p-2 border rounded" required>
                </div>

                <div>
                    <label class="font-semibold">Unité Concernée</label>
                    <input type="text" name="unite_concernee" placeholder="Unité"
                        class="w-full p-2 border rounded" required>
                </div>

                <div>
                    <label class="font-semibold">Objet de la Modification</label>
                    <input type="text" name="objet_modif" placeholder="Objet"
                        class="w-full p-2 border rounded" required>
                </div>

                <div>
                    <label class="font-semibold">Motif</label>
                    <textarea name="motif" placeholder="Motif"
                        class="w-full p-2 border rounded"></textarea>
                </div>

                <div>
                    <label class="font-semibold">Repère</label>
                    <input type="text" name="repere" placeholder="Repère"
                        class="w-full p-2 border rounded" required>
                </div>

                <div>
                    <label class="font-semibold">Fonction</label>
                    <input type="text" name="fonction" placeholder="Fonction"
                        class="w-full p-2 border rounded" required>
                </div>

                <div>
                    <label class="font-semibold">Situation Existante</label>
                    <textarea name="situation_existante" placeholder="Situation existante"
                        class="w-full p-2 border rounded" required></textarea>
                </div>

                <div>
                    <label class="font-semibold">Situation Souhaitée</label>
                    <textarea name="situation_souhaitee" placeholder="Situation souhaitée"
                        class="w-full p-2 border rounded" required></textarea>
                </div>

                {{-- 🧩 Champ pour pièces jointes --}}
                <div>
                    <label for="pieces_jointes" class="font-semibold">Pièces jointes</label>
                    <input type="file" name="pieces_jointes[]" multiple
                        class="w-full p-2 border rounded bg-gray-50">
                    <p class="mt-1 text-sm text-gray-500">
                        Formats autorisés : PDF, DOCX, JPG, PNG — taille max 5 Mo par fichier.
                    </p>
                </div>

                {{-- 🏢 Sélection des structures spécialisées --}}
                <div>
                <label for="pieces_jointes" class="font-semibold">Structures spécialisées à impliquer</label>
                <div class="grid grid-cols-2 gap-2 p-2 border rounded">
                    @foreach($structures as $structure)
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="structures_specialisees[]" value="{{ $structure->id }}">
                        <span>{{ $structure->name }}</span>
                    </label>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500">Vous pouvez ne sélectionner aucune structure si non nécessaire.</p>
                </div>
                
                <div class="flex justify-between pt-4">
                    <a href="{{ route('demandes.index') }}"
                        class="inline-block px-4 py-2 text-white bg-gray-600 rounded hover:bg-gray-700">
                        ← Retour
                    </a>

                    <button type="submit"
                        class="px-6 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Enregistrer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>