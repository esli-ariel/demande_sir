<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructureSpecialisee extends Model
{
    protected $table = 'structures_specialisees';

    //
    public function demandes()
{
    return $this->belongsToMany(Demande::class, 'demande_structure_specialisee', 'structure_id', 'demande_id');
}
    public function users()
    {
        return $this->belongsToMany(User::class, 'structure_specialisee_user', 'structure_id', 'user_id')->withTimestamps();
    }

    public function decisions()
    {
        return $this->hasMany(DemandeValidation::class, 'structure_id');
    }
}
