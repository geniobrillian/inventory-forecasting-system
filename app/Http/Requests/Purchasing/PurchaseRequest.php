<?php

namespace App\Http\Requests\Purchasing;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('order_date') && !$this->has('purchase_date')) {
            $merge['purchase_date'] = $this->order_date;
        }
        if ($this->has('expected_delivery_date') && !$this->has('expected_date')) {
            $merge['expected_date'] = $this->expected_delivery_date;
        }

        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as $idx => $item) {
                if (isset($item['unit_price']) && !isset($item['unit_cost'])) {
                    $items[$idx]['unit_cost'] = $item['unit_price'];
                }
            }
            $merge['items'] = $items;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'purchase_number' => ['nullable', 'string', 'max:50'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in([Purchase::STATUS_DRAFT, Purchase::STATUS_ORDERED])],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Pilih supplier pemasok.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
            'warehouse_id.required' => 'Pilih gudang tujuan penerimaan barang.',
            'warehouse_id.exists' => 'Gudang yang dipilih tidak valid.',
            'purchase_date.required' => 'Tanggal PO wajib diisi.',
            'expected_date.after_or_equal' => 'Estimasi tanggal tiba tidak boleh sebelum tanggal PO.',
            'items.required' => 'Minimal harus ada 1 item produk yang dipesan.',
            'items.min' => 'Minimal harus ada 1 item produk yang dipesan.',
            'items.*.product_id.required' => 'Pilih produk untuk setiap baris pesanan.',
            'items.*.product_id.distinct' => 'Produk pada baris pesanan tidak boleh duplikat.',
            'items.*.quantity.required' => 'Kuantitas pesanan wajib diisi.',
            'items.*.quantity.min' => 'Kuantitas pesanan minimal 1.',
            'items.*.unit_cost.required' => 'Harga beli satuan wajib diisi.',
            'items.*.unit_cost.min' => 'Harga beli satuan tidak boleh bernilai negatif.',
        ];
    }
}
