<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Détail de la Demande</h2>
    </x-slot>

    <div class="py-6">
        @if ($errors->any())
            <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
        <div class="p-6 bg-white rounded shadow">
            <h3 class="mb-4 text-lg font-bold">Informations du demandeur</h3>

            <p><b>Nom du demandeur :</b> {{ $demande->nom }}</p>
            <p><b>Utilisateur associé :</b> {{ $demande->user->name }}</p>
            <p><b>Structure :</b> {{ $demande->structure }}</p>
            <p><b>Unité concernée :</b> {{ $demande->unite_concernee }}</p>
            <p><b>Fonction :</b> {{ $demande->fonction }}</p>
            <p><b>Date de création :</b> {{ $demande->date_creation }}</p>
        </div>

        <div class="p-6 mt-6 bg-white rounded shadow">
            <h3 class="mb-4 text-lg font-bold">Objet de la demande</h3>
            <p><b>Objet de la modification :</b> {{ $demande->objet_modif }}</p>
            <p><b>Motif :</b> {{ $demande->motif }}</p>
            <p><b>Repère :</b> {{ $demande->repere }}</p>
        </div>

        <div class="p-6 mt-6 bg-white rounded shadow">
            <h3 class="mb-4 text-lg font-bold">Situations</h3>
            <p><b>Situation existante :</b><br> {{ $demande->situation_existante }}</p>
            <p><b>Situation souhaitée :</b><br> {{ $demande->situation_souhaitee }}</p>
        </div>
    </div>
    <div class="mb-4">
        <strong>Statut :</strong>
        <span class="{{ $demande->getStatutBadgeClass() }}">
            {{ ucfirst(str_replace('_',' ', $demande->statut)) }}
        </span>
    </div>
<div class="p-6 mt-6 bg-white rounded-lg shadow-md">
    <h3 class="mb-4 text-lg font-semibold">📎 Pièces jointes</h3>

    @if($demande->piecesJointes->count() > 0)
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($demande->piecesJointes as $piece)
                <div class="overflow-hidden border rounded-lg shadow-sm bg-gray-50">
                    <div class="p-2 text-sm text-gray-700">
                        <strong>{{ basename($piece->chemin_fichier) }}</strong>
                        <p class="text-xs text-gray-500">
                            Type : {{ strtoupper($piece->type_document) }} — 
                            Ajouté par : {{ $piece->uploader->name ?? 'Inconnu' }}
                        </p>
                    </div>

                    {{-- 🔍 Prévisualisation selon le type --}}
                    <div class="p-2 bg-white border-t">
                        @php
                            $extension = strtolower($piece->type_document);
                            $url = asset('storage/' . $piece->chemin_fichier);
                        @endphp

                        @if(in_array($extension, ['jpg','jpeg','png','gif']))
                            {{-- 🖼️ Image directement affichée --}}
                            <img src="{{ $url }}" alt="Pièce jointe" class="object-cover w-full h-48 rounded">
                        @elseif($extension === 'pdf')
                            {{-- 📄 Aperçu PDF avec iframe --}}
                            <iframe src="{{ $url }}" class="w-full h-64 border-0 rounded" allowfullscreen></iframe>
                        @elseif(in_array($extension, ['doc','docx']))
                            {{-- 🧾 Affichage Word via Google Docs Viewer --}}
                            <iframe src="https://docs.google.com/gview?url={{ urlencode($url) }}&embedded=true"
                                    class="w-full h-64 border-0 rounded" allowfullscreen></iframe>
                        @else
                            {{-- 🗂️ Fichiers non prévisualisables --}}
                            <p class="p-2 italic text-gray-500">
                                Ce type de fichier ne peut pas être prévisualisé.
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="italic text-gray-500">Aucune pièce jointe disponible.</p>
    @endif
</div>
<div class="p-6 mt-6 bg-white rounded-lg shadow-md"> 
    @if($demande->structuresSpecialisees->count() > 0)
    <p><b>Structures spécialisées concernées :</b></p>
    <ul class="list-disc list-inside">
        @foreach($demande->structuresSpecialisees as $structure)
            <li>{{ $structure->nom }}</li>
        @endforeach
    </ul>
@else
    <p><i>Aucune structure spécialisée sélectionnée.</i></p>
@endif
</div>

    <!-- Bouton soumettre -->
            @if($demande->statut === 'brouillon')
                <form action="{{ route('demandes.submit', $demande) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">
                        Soumettre au responsable
                    </button>
                </form>
            @endif
            <button type="submit" class="px-4 py-2 text-white bg-red-500 rounded" onclick="window.history.back()">
                    Retour
                </button>
</x-app-layout>
