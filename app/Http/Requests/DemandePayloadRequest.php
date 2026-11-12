<?php

namespace App\Http\Requests;

use App\Models\Demande;
use Illuminate\Foundation\Http\FormRequest;

class DemandePayloadRequest extends FormRequest
{
    public const FIELDS = ['structure','unite_concernee','objet_modif','motif','repere','fonction','situation_existante','situation_souhaitee'];

    public function authorize(): bool
    {
        $demande = $this->route('demande');
        return $demande instanceof Demande
            ? ($this->user()?->can('update', $demande) ?? false)
            : ($this->user()?->can('create', Demande::class) ?? false);
    }

    public function rules(): array
    {
        $updating = $this->route('demande') instanceof Demande;
        $rules = [];
        foreach (self::FIELDS as $field) {
            $required = ! $updating || in_array($field,['structure','objet_modif'],true);
            $rules[$field] = ['bail',$required ? 'required' : 'nullable','string',in_array($field,['motif','situation_existante','situation_souhaitee'],true) ? 'max:10000' : 'max:255'];
        }
        return $rules + [
            'structures_included'=>['sometimes','boolean'],
            'structures_specialisees'=>['sometimes','array','list','max:100'],
            'structures_specialisees.*'=>['bail','integer','distinct','exists:structures_specialisees,id'],
            'pieces_jointes'=>['sometimes','array','list','max:10'],
            'pieces_jointes.*'=>['required','file','mimes:jpg,jpeg,png,pdf,doc,docx','max:5120'],
        ];
    }
}
