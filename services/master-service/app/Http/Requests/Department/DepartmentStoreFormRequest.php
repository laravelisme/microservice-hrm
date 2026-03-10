<?php

namespace App\Http\Requests\Department;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentStoreFormRequest extends FormRequest
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
            'department_name' => 'required|string|max:255',
            'is_hr' => 'required|in:0,1',
            'company_id' => 'required|exists:companies,id',
        ];
    }
}
