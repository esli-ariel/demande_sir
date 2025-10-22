<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Modifier Demande</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        {{-- 🎨 Image de fond filigrane --}}
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 110%;">
        </div>

        {{-- ✅ Contenu du formulaire au-dessus du fond --}}
        <div class="relative z-10 py-10 px-6 sm:px-12 lg:px-24">
            
            {{-- 🔔 Gestion des erreurs --}}
            @if ($errors->any())
                <div class="p-4 mb-6 text-red-800 bg-red-100 border border-red-300 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ✏️ Formulaire de mise à jour --}}
            <form action="{{ route('demandes.update', $demande) }}" method="POST" class="p-6 bg-white rounded-lg shadow-md space-y-4" enctype="multipart/form-data">
                @csrf 
                @method('PUT')

                <div>
                    <label class="font-semibold">Objet de la Modification</label>
                    <input type="text" name="objet_modif"
                           value="{{ old('objet_modif', $demande->objet_modif) }}"
                           class="w-full p-2 border rounded">
                </div>

                <div>
                    <label class="font-semibold">Structure</label>
                    <input type="text" name="structure"
                           value="{{ old('structure', $demande->structure) }}"
                           class="w-full p-2 border rounded">
                </div>

                <div>
                    <label class="font-semibold">Unité Concernée</label>
                    <input type="text" name="unite_concernee"
                           value="{{ old('unite_concernee', $demande->unite_concernee) }}"
                           class="w-full p-2 border rounded">
                </div>

                <div>
                    <label class="font-semibold">Repère</label>
                    <input type="text" name="repere"
                           value="{{ old('repere', $demande->repere) }}"
                           class="w-full p-2 border rounded">
                </div>

                <div>
                    <label class="font-semibold">Fonction</label>
                    <input type="text" name="fonction"
                           value="{{ old('fonction', $demande->fonction) }}"
                           class="w-full p-2 border rounded">
                </div>

                <div>
                    <label class="font-semibold">Situation Existante</label>
                    <textarea name="situation_existante"
                              class="w-full p-2 border rounded">{{ old('situation_existante', $demande->situation_existante) }}</textarea>
                </div>

                <div>
                    <label class="font-semibold">Motif</label>
                    <textarea name="motif"
                              class="w-full p-2 border rounded">{{ old('motif', $demande->motif) }}</textarea>
                </div>

                <div>
                    <label class="font-semibold">Situation Souhaitée</label>
                    <textarea name="situation_souhaitee"
                              class="w-full p-2 border rounded">{{ old('situation_souhaitee', $demande->situation_souhaitee) }}</textarea>
                </div>

                {{-- 📎 Pièces jointes (ajout optionnel) --}}
                <div>
                    <label class="font-semibold">Ajouter de nouvelles pièces jointes</label>
                    <input type="file" name="pieces_jointes[]" multiple
                           class="w-full p-2 border rounded bg-gray-50">
                    <p class="text-sm text-gray-500 mt-1">
                        Formats autorisés : PDF, DOCX, JPG, PNG — taille max 5 Mo par fichier.
                    </p>
                </div>

                {{-- 🔍 Liste des pièces jointes existantes --}}
                @if($demande->piecesJointes->count() > 0)
                    <div class="mt-4">
                        <h3 class="font-bold">📂 Pièces jointes existantes :</h3>
                        <ul class="list-disc list-inside">
                            @foreach($demande->piecesJointes as $piece)
                                <li class="flex items-center justify-between">
                                    <a href="{{ asset('storage/' . $piece->chemin_fichier) }}" target="_blank"
                                       class="text-blue-600 underline">
                                        {{ basename($piece->chemin_fichier) }}
                                    </a>
                                    <form action="{{ route('pieces.destroy', $piece) }}" method="POST" onsubmit="return confirm('Supprimer ce fichier ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm hover:underline">Supprimer</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-between pt-4">
                    <a href="{{ route('demandes.index') }}"
                       class="inline-block px-4 py-2 text-white bg-gray-600 rounded hover:bg-gray-700">
                        ← Retour
                    </a>

                    <button type="submit"
                            class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Mettre à jour
                    </button>
                </div>
            </form>

            {{-- 📨 Soumission vers le responsable --}}
            @if($demande->statut === 'brouillon')
                <form action="{{ route('demandes.submit', $demande) }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit" class="px-6 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Soumettre au responsable
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
