<?php

namespace App\Http\Requests\Company;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateFormRequest extends FormRequest
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
            'company_name' => 'required|string|max:255',
            'level' => 'required|in:HOLDING,COMPANY',

        ];
    }
}
