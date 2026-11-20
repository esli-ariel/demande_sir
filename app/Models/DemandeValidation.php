<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeValidation extends Model
{
    //
    protected $table = 'demandes_validations';
    protected $fillable = [
        'demande_id',
        'structure_id',
        'role',
        'user_id',
        'decision',
        'visa',
        'commentaire',
        'date_validation', 'signer_name', 'signer_structure_name', 'signed_at',
        'visa_action', 'status_before', 'status_after',
    ];

    protected function casts(): array
    {
        return ['signed_at'=>'datetime'];
    }

    protected static function booted(): void
    {
        $protect = function (self $visa) {
            if ($visa->getRawOriginal('signed_at') !== null) {
                throw new \LogicException('Un visa nominatif enregistré est conservé sans modification ni suppression.');
            }
        };
        static::updating($protect);
        static::deleting($protect);
    }

    public function demande() {
        return $this->belongsTo(Demande::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function structure()
    {
        return $this->belongsTo(StructureSpecialisee::class, 'structure_id');
    }
}


