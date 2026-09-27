<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('transaction_type') || empty($this->input('transaction_type'))) {
            $this->merge(['transaction_type' => InventoryTransaction::TYPE_PURCHASE]);
        }
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'transaction_type' => [
                'required',
                'string',
                Rule::in([
                    InventoryTransaction::TYPE_PURCHASE,
                    InventoryTransaction::TYPE_RETURN_IN,
                    InventoryTransaction::TYPE_INITIAL_STOCK,
                    InventoryTransaction::TYPE_ADJUSTMENT_IN,
                ]),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Pilih produk yang akan dimasukkan.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'warehouse_id.required' => 'Pilih gudang tujuan penyimpanan.',
            'warehouse_id.exists' => 'Gudang yang dipilih tidak valid.',
            'quantity.required' => 'Kuantitas barang masuk wajib diisi.',
            'quantity.min' => 'Kuantitas barang masuk minimal 1.',
            'transaction_type.required' => 'Jenis transaksi masuk wajib dipilih.',
        ];
    }
}
