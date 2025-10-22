<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeValidation extends Model
{
    //
    protected $table = 'demandes_validations';
    protected $fillable = [
        'demande_id',
        'role',
        'user_id',
        'decision',
        'visa',
        'commentaire',
        'date_validation'
    ];

    public function demande() {
        return $this->belongsTo(Demande::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}


