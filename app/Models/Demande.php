<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'structure',
        'unite',
        'objet',
        'motif',
        'repere',
        'situation_existante',
        'situation_souhaitee',
        'statut',
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
    public function getStatutBadgeClass()
{
    return match ($this->statut) {
        'brouillon'              => 'text-gray-500 bg-gray-400 text-white px-2 py-1 rounded',
        'en_attente_validation'  => 'text-yellow-500 bg-yellow-500 text-white px-2 py-1 rounded',
        'validee_responsable'    => 'text-blue-500 bg-blue-500 text-white px-2 py-1 rounded',
        'validee_finale'         => 'text-green-500 bg-green-600 text-white px-2 py-1 rounded',
        'rejete'                 => 'text-red-500 bg-red-600 text-white px-2 py-1 rounded',
        'cloturee'               => 'text-gray-500 bg-gray-800 text-white px-2 py-1 rounded',
        default                  => 'text-gray-500 bg-gray-200 text-black px-2 py-1 rounded',
    };
}

}
