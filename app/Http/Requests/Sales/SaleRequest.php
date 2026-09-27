<?php

namespace App\Http\Requests\Sales;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'sale_date' => ['required', 'date'],
            'status' => ['nullable', 'string', Rule::in([Sale::STATUS_DRAFT, Sale::STATUS_COMPLETED])],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Pilih gudang sumber stok penjualan.',
            'warehouse_id.exists' => 'Gudang yang dipilih tidak valid.',
            'sale_date.required' => 'Tanggal penjualan wajib diisi.',
            'items.required' => 'Minimal harus ada 1 item produk yang dijual.',
            'items.min' => 'Minimal harus ada 1 item produk yang dijual.',
            'items.*.product_id.required' => 'Pilih produk untuk setiap baris penjualan.',
            'items.*.product_id.distinct' => 'Produk pada baris penjualan tidak boleh duplikat.',
            'items.*.quantity.required' => 'Kuantitas penjualan wajib diisi.',
            'items.*.quantity.min' => 'Kuantitas penjualan minimal 1.',
            'items.*.unit_price.required' => 'Harga jual satuan wajib diisi.',
            'items.*.unit_price.min' => 'Harga jual satuan tidak boleh bernilai negatif.',
        ];
    }
}
