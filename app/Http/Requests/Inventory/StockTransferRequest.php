<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Pilih produk yang akan ditransfer.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'from_warehouse_id.required' => 'Pilih gudang asal transfer.',
            'to_warehouse_id.required' => 'Pilih gudang tujuan transfer.',
            'to_warehouse_id.different' => 'Gudang tujuan transfer tidak boleh sama dengan gudang asal.',
            'quantity.required' => 'Kuantitas transfer wajib diisi.',
            'quantity.min' => 'Kuantitas transfer minimal 1 unit.',
        ];
    }
}
