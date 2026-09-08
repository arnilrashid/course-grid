<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        $rules = [
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ];

        if ($role->name !== 'admin') {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . $role->id;
        }

        return $rules;
    }
}
