<?php

namespace App\Modules\Category\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        // Seuls les admins peuvent créer/modifier des catégories
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules()
    {
        $categoryId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $categoryId,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{6}$/',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Le nom de la catégorie est requis',
            'slug.unique' => 'Ce slug est déjà utilisé',
            'color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB)',
            'parent_id.exists' => 'La catégorie parente n\'existe pas',
        ];
    }
}
