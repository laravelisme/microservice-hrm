<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequestForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appName' => 'required|string',
            'appToken' => 'required|string',
            'client' => 'required|string',
            'data' => 'required|array',
            'data.username' => 'required|string',
            'data.password' => 'required|string',
            'data.unique_id' => 'nullable|string',
            'data.device_info' => 'nullable|string',
            'data.bundle_id' => 'nullable|string',
            'data.device_token' => 'nullable|string',
        ];
    }
}
