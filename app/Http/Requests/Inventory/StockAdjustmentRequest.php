<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'actual_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['required', 'string', 'max:1000'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Pilih produk yang akan disesuaikan.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'warehouse_id.required' => 'Pilih gudang persediaan.',
            'actual_quantity.required' => 'Kuantitas fisik aktual wajib diisi.',
            'actual_quantity.min' => 'Kuantitas fisik aktual tidak boleh bernilai negatif.',
            'notes.required' => 'Alasan penyesuaian stok / hasil stock opname wajib diisi.',
        ];
    }
}
