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
}
