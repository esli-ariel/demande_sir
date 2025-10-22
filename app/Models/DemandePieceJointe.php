<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandePieceJointe extends Model
{
    //
   use HasFactory;

    // ✅ Nom explicite de la table
    protected $table = 'demandes_pieces_jointes';

    protected $fillable = [
        'demande_id',
        'chemin_fichier',
        'type_document',
        'uploaded_by',
    ];

    // Relations
    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
