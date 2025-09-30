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
        'brouillon'              => 'bg-gray-400 text-white px-2 py-1 rounded',
        'en_attente_validation'  => 'bg-yellow-500 text-white px-2 py-1 rounded',
        'validee_responsable'    => 'bg-blue-500 text-white px-2 py-1 rounded',
        'validee_finale'         => 'bg-green-600 text-white px-2 py-1 rounded',
        'rejete'                 => 'bg-red-600 text-white px-2 py-1 rounded',
        'cloturee'               => 'bg-gray-800 text-white px-2 py-1 rounded',
        default                  => 'bg-gray-200 text-black px-2 py-1 rounded',
    };
}

}
