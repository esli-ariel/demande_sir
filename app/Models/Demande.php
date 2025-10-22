<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom',
        'structure',
        'date_creation',
        'unite_concernee',
        'repere',
        'fonction',
        'motif',
        'objet_modif',
        'situation_existante',
        'situation_souhaitee',
        'statut',
        'numero_dma',
        'date_debut',
        'date_fin',
        'travaux_realises'
    
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validations()
    {
        return $this->hasMany(Validation::class);
    }

    public function piecesJointes()
    {
        return $this->hasMany(DemandePieceJointe::class);
    }
    public function getStatutBadgeClass()
{
    return match ($this->statut) {
        // 📝 Brouillon
        'brouillon'                   => 'text-gray-700 bg-gray-300 px-2 py-1 rounded',

        // 📩 Soumise
        'soumise'                     => 'text-yellow-700 bg-yellow-300 px-2 py-1 rounded',

        // ✅ Exploitation
        'validee_exploitation'        => 'text-blue-700 bg-blue-200 px-2 py-1 rounded',
        'refusee_exploitation'        => 'text-red-700 bg-red-200 px-2 py-1 rounded',

        // ✅ DTS
        'validee_dts'                 => 'text-indigo-700 bg-indigo-200 px-2 py-1 rounded',
        'refusee_dts'                 => 'text-red-700 bg-red-300 px-2 py-1 rounded',

        // ✅ Structures spécialisées
        'validee_structure_specialisee' => 'text-green-700 bg-green-200 px-2 py-1 rounded',
        'refusee_structure_specialisee' => 'text-red-800 bg-red-400 px-2 py-1 rounded',

        // ✅ Contrôle avancé
        'validee_controle_avancee'    => 'text-purple-700 bg-purple-200 px-2 py-1 rounded',
        'refusee_controle_avancee'    => 'text-red-900 bg-red-500 px-2 py-1 rounded',

        // 🔧 Traitement par le service technique
        'en_cours_traitement'         => 'text-orange-700 bg-orange-200 px-2 py-1 rounded',
        'terminee_agent'              => 'text-teal-700 bg-teal-200 px-2 py-1 rounded',

        // 📦 Réception finale
        'cloturee_receptionnee'       => 'text-gray-100 bg-gray-800 px-2 py-1 rounded',

        // Défaut si jamais un statut inconnu arrive
        default                       => 'text-gray-600 bg-gray-100 px-2 py-1 rounded',
    };
}


}
