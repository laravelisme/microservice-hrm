<?php

namespace App\Http\Requests\Auth;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;

class RefreshRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return JwtHelper::hasRole($this, ['super-admin']);
    }

    public function rules(): array
    {
        return [
            'refresh_token' => 'required|string',
        ];
    }
}
