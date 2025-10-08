<?php

namespace App\Modules\Signalement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignalementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|in:objet,personne',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date_loss' => 'required|date',
            'location' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'string',
            'status' => 'required|in:en_cours,retrouve,faux',
        ];
    }
}

