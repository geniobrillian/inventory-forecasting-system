<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper($this->code)]);
        }
    }

    public function rules(): array
    {
        $unitId = $this->route('unit')?->id ?? $this->route('unit');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'code')->ignore($unitId)->whereNull('deleted_at'),
            ],
            'symbol' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama satuan unit wajib diisi.',
            'code.required' => 'Kode unit wajib diisi.',
            'code.unique' => 'Kode unit sudah digunakan.',
        ];
    }
}
