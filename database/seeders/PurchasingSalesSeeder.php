<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use App\Services\SalesService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PurchasingSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchaseService = app(PurchaseService::class);
        $salesService = app(SalesService::class);

        $admin = User::first();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $products = Product::all();

        if ($suppliers->isEmpty() || $warehouses->isEmpty() || $products->isEmpty()) {
            return;
        }

        $supplierA = $suppliers->first();
        $supplierB = $suppliers->count() > 1 ? $suppliers->get(1) : $supplierA;
        $whMain = $warehouses->first();
        $whSecondary = $warehouses->count() > 1 ? $warehouses->get(1) : $whMain;

        // 1. Purchase Order 1: RECEIVED (Fully received into warehouse)
        $po1 = $purchaseService->createPurchase([
            'purchase_number' => 'PO-' . now()->format('Ym') . '-0001',
            'supplier_id' => $supplierA->id,
            'warehouse_id' => $whMain->id,
            'order_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'expected_delivery_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'status' => 'ORDERED',
            'shipping_cost' => 50000,
            'notes' => 'Pengadaan stok bulanan batch 1',
            'created_by' => $admin->id,
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 100,
                    'unit_price' => $products[0]->purchase_price,
                    'discount_percent' => 0,
                    'tax_percent' => 11,
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 50,
                    'unit_price' => $products[1]->purchase_price,
                    'discount_percent' => 5,
                    'tax_percent' => 11,
                ]
            ]
        ]);

        // Receive PO 1 fully
        $purchaseService->receiveItems($po1, [
            'received_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'notes' => 'SJ Supplier No. 998812 - Diterima lengkap dalam kondisi prima',
            'items' => $po1->items->map(function ($item) {
                return [
                    'purchase_item_id' => $item->id,
                    'received_quantity' => $item->quantity,
                ];
            })->toArray(),
        ]);

        // 2. Purchase Order 2: PARTIALLY_RECEIVED
        if ($products->count() > 3) {
            $po2 = $purchaseService->createPurchase([
                'purchase_number' => 'PO-' . now()->format('Ym') . '-0002',
                'supplier_id' => $supplierB->id,
                'warehouse_id' => $whMain->id,
                'order_date' => Carbon::now()->subDays(4)->format('Y-m-d'),
                'expected_delivery_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'status' => 'ORDERED',
                'shipping_cost' => 25000,
                'notes' => 'Pengadaan sparepart & komponen elektronika',
                'created_by' => $admin->id,
                'items' => [
                    [
                        'product_id' => $products[2]->id,
                        'quantity' => 80,
                        'unit_price' => $products[2]->purchase_price,
                        'discount_percent' => 0,
                        'tax_percent' => 11,
                    ],
                    [
                        'product_id' => $products[3]->id,
                        'quantity' => 60,
                        'unit_price' => $products[3]->purchase_price,
                        'discount_percent' => 0,
                        'tax_percent' => 11,
                    ]
                ]
            ]);

            // Receive partially (only 40 out of 80 for product 2, 0 for product 3)
            $purchaseService->receiveItems($po2, [
                'received_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'notes' => 'SJ Batch 1 - Diterima sebagian (40 pcs)',
                'items' => [
                    [
                        'purchase_item_id' => $po2->items[0]->id,
                        'received_quantity' => 40,
                    ],
                    [
                        'purchase_item_id' => $po2->items[1]->id,
                        'received_quantity' => 0,
                    ],
                ]
            ]);
        }

        // 3. Purchase Order 3: DRAFT
        if ($products->count() > 2) {
            $purchaseService->createPurchase([
                'purchase_number' => 'PO-' . now()->format('Ym') . '-0003',
                'supplier_id' => $supplierA->id,
                'warehouse_id' => $whSecondary->id,
                'order_date' => Carbon::now()->format('Y-m-d'),
                'expected_delivery_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'status' => 'DRAFT',
                'shipping_cost' => 0,
                'notes' => 'Draft rencana pengadaan minggu depan',
                'created_by' => $admin->id,
                'items' => [
                    [
                        'product_id' => $products[0]->id,
                        'quantity' => 20,
                        'unit_price' => $products[0]->purchase_price,
                        'discount_percent' => 0,
                        'tax_percent' => 0,
                    ]
                ]
            ]);
        }

        // 4. Sales 1: COMPLETED (Stock deducted)
        $salesService->createSale([
            'invoice_number' => 'INV-' . now()->format('Ym') . '-0001',
            'warehouse_id' => $whMain->id,
            'customer_name' => 'PT Mitra Sejahtera Bersama',
            'customer_phone' => '081234567890',
            'sale_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'status' => 'COMPLETED',
            'payment_method' => 'TRANSFER',
            'payment_status' => 'PAID',
            'shipping_cost' => 15000,
            'paid_amount' => 1500000,
            'notes' => 'Pesanan rutin kantor cabang',
            'created_by' => $admin->id,
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 15,
                    'unit_price' => $products[0]->selling_price,
                    'discount_percent' => 2,
                    'tax_percent' => 11,
                ]
            ]
        ]);

        // 5. Sales 2: COMPLETED (Cash retail)
        if ($products->count() > 1) {
            $salesService->createSale([
                'invoice_number' => 'INV-' . now()->format('Ym') . '-0002',
                'warehouse_id' => $whMain->id,
                'customer_name' => 'Bpk. Hendra Gunawan (Retail)',
                'customer_phone' => '085712349999',
                'sale_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'status' => 'COMPLETED',
                'payment_method' => 'CASH',
                'payment_status' => 'PAID',
                'shipping_cost' => 0,
                'paid_amount' => 500000,
                'notes' => 'Pembelian langsung di toko / gudang',
                'created_by' => $admin->id,
                'items' => [
                    [
                        'product_id' => $products[1]->id,
                        'quantity' => 5,
                        'unit_price' => $products[1]->selling_price,
                        'discount_percent' => 0,
                        'tax_percent' => 11,
                    ]
                ]
            ]);
        }

        // 6. Sales 3: DRAFT
        $salesService->createSale([
            'invoice_number' => 'INV-' . now()->format('Ym') . '-0003',
            'warehouse_id' => $whMain->id,
            'customer_name' => 'Toko Cahaya Abadi',
            'customer_phone' => '081399887766',
            'sale_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'DRAFT',
            'payment_method' => 'TRANSFER',
            'payment_status' => 'UNPAID',
            'shipping_cost' => 20000,
            'paid_amount' => 0,
            'notes' => 'Menunggu konfirmasi pembayaran DP dari pelanggan',
            'created_by' => $admin->id,
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 10,
                    'unit_price' => $products[0]->selling_price,
                    'discount_percent' => 0,
                    'tax_percent' => 11,
                ]
            ]
        ]);
    }
}
