<?php

namespace App\Modules\Message\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'signalement_id' => 'required|exists:signalements,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'signalement_id.required' => 'Le signalement est requis',
            'signalement_id.exists' => 'Le signalement n\'existe pas',
            'receiver_id.required' => 'Le destinataire est requis',
            'receiver_id.exists' => 'Le destinataire n\'existe pas',
            'content.required' => 'Le contenu du message est requis',
            'content.max' => 'Le message ne peut pas dépasser 2000 caractères',
        ];
    }
}
