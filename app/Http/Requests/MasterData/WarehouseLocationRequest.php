<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseLocationRequest extends FormRequest
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
        $warehouse = $this->route('warehouse');
        $warehouseId = is_object($warehouse) ? $warehouse->id : ($warehouse ?? $this->input('warehouse_id'));
        $locationId = $this->route('location')?->id ?? $this->route('location');

        return [
            'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouse_locations', 'code')
                    ->where(fn ($query) => $query->where('warehouse_id', $warehouseId))
                    ->ignore($locationId)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['RACK', 'ZONE', 'BIN', 'AISLE', 'DEFAULT', 'OTHER'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode lokasi/rak/zona wajib diisi.',
            'code.unique' => 'Kode lokasi sudah terdaftar pada gudang ini.',
            'name.required' => 'Nama lokasi wajib diisi.',
            'type.required' => 'Tipe lokasi wajib dipilih.',
        ];
    }
}
