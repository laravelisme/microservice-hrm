<?php

namespace App\Http\Requests\GrupJamKerja;

use App\Services\JwtHelper;
use Illuminate\Foundation\Http\FormRequest;

class GrupJamKerjaStoreFormRequest extends FormRequest
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
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $rules = [
            'name' => 'required|string|max:256',
            'start' => 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/',
            'end' => 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/',
            'min_check_in' => 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/',
            'max_check_in' => 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/',
            'min_check_out' => 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/',
            'max_check_out' => 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/',
        ];

        foreach ($days as $day) {
            $rules["{$day}_start"] = 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/';
            $rules["{$day}_end"] = 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/';
            $rules["{$day}_min_check_in"] = 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/';
            $rules["{$day}_max_check_in"] = 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/';
            $rules["{$day}_min_check_out"] = 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/';
            $rules["{$day}_max_check_out"] = 'nullable|string|size:5|regex:/^\d{2}:\d{2}$/';
            $rules["{$day}_type"] = 'nullable|string|in:WEEKDAY,WEEKEND,FULL,OFF';
        }

        return $rules;
    }
}
