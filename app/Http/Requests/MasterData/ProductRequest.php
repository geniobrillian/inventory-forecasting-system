<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sku')) {
            $this->merge(['sku' => strtoupper($this->sku)]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($productId)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0', 'max:365'],
            'forecast_method' => ['required', 'string', Rule::in(['MOVING_AVERAGE', 'EXPONENTIAL_SMOOTHING'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
            'minimum_stock.required' => 'Minimum stok wajib diisi.',
            'minimum_stock.min' => 'Minimum stok tidak boleh negatif.',
            'lead_time_days.required' => 'Lead time wajib diisi.',
            'forecast_method.required' => 'Metode peramalan default wajib dipilih.',
        ];
    }
}
