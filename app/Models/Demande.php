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
}
