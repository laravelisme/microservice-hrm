<?php

namespace App\Http\Requests\SaldoCuti;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;

class SaldoCutiUpdateFormRequest extends FormRequest
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
            'jenis' => 'required|string',
            'jumlah' => 'required|integer',
            'jabatan_id' => 'required|integer|exists:jabatans,id',
        ];
    }
}
