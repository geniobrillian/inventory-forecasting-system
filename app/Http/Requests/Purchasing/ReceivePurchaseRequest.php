<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('received_date') && !$this->has('receiving_date')) {
            $this->merge(['receiving_date' => $this->received_date]);
        }
    }

    public function rules(): array
    {
        return [
            'receiving_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.received_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiving_date.required' => 'Tanggal penerimaan fisik wajib diisi.',
            'items.required' => 'Daftar item penerimaan tidak boleh kosong.',
            'items.*.received_quantity.required' => 'Jumlah barang diterima wajib diisi.',
            'items.*.received_quantity.min' => 'Jumlah barang diterima minimal 0.',
        ];
    }
}
