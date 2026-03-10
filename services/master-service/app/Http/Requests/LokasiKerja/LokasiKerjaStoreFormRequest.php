<?php

namespace App\Http\Requests\LokasiKerja;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;

class LokasiKerjaStoreFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return JwtHelper::hasRole($this, ['super-admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
