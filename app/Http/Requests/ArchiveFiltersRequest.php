<?php

namespace App\Http\Requests;

use App\Models\Demande;
use Illuminate\Foundation\Http\FormRequest;

final class ArchiveFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Demande::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'q'=>['nullable','string','max:150'],
            'unite'=>['nullable','string','max:255'],
            'annee'=>['nullable','integer','min:1000','max:9999'],
            'du'=>['nullable','date_format:Y-m-d','after_or_equal:1000-01-01','before_or_equal:9999-12-31'],
            'au'=>['nullable','date_format:Y-m-d','after_or_equal:1000-01-01','before_or_equal:9999-12-31', ...($this->filled('du') ? ['after_or_equal:du'] : [])],
            'page'=>['nullable','integer','min:1','max:100000'],
        ];
    }

    public function attributes(): array
    {
        return ['q'=>'recherche','unite'=>'unité','annee'=>'année de clôture','du'=>'début de période','au'=>'fin de période'];
    }
}
