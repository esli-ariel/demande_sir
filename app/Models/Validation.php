<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'validator_id',
        'type',
        'statut',
        'commentaire',
        'date_validation',
    ];

    // Relations
    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}
