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
        'travaux_realises',
        'closed_at',
    
    ];

    protected function casts(): array
    {
        return ['closed_at'=>'datetime'];
    }

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
        return 'dma-status dma-status-'.\App\Support\DmaStatus::group($this->statut);
    }

// Demande.php
public function structuresSpecialisees()
{
    return $this->belongsToMany(StructureSpecialisee::class, 'demande_structure_specialisee', 'demande_id', 'structure_id');
}



}
