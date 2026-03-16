<?php

namespace App\Http\Requests\HariLibur;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HariLiburStoreFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return JwtHelper::hasRole($this, ['super-admin']);
    }

    protected function prepareForValidation(): void
    {
        foreach (['is_cuti_bersama', 'is_umum', 'is_repeat'] as $key) {
            if ($this->has($key)) {
                $this->merge([
                    $key => filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'hari_libur' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],

            'is_cuti_bersama' => ['required', 'boolean'],
            'is_umum' => ['required', 'boolean'],
            'is_repeat' => ['required', 'boolean'],

            'company_ids' => [
                'exclude_if:is_umum,true',
                'required',
                'array',
                'min:1',
            ],
            'company_ids.*' => [
                'integer',
                'exists:companies,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_ids.required' => 'Perusahaan wajib dipilih jika "Terapkan disemua perusahaan" = Tidak.',
            'company_ids.min' => 'Minimal pilih 1 perusahaan.',
        ];
    }
}
