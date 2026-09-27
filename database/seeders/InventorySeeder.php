<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(InventoryService $inventoryService): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        $whJakarta = Warehouse::where('code', 'WH-JKT01')->first();
        $whSurabaya = Warehouse::where('code', 'WH-SBY02')->first();

        if (!$whJakarta || !$whSurabaya) {
            return;
        }

        $products = Product::all();

        // 1. Initial Inbound (Stock In from PO / Suppliers)
        $initialStocks = [
            'PRD-SSD-1TB' => [
                ['wh' => $whJakarta, 'qty' => 100, 'ref' => 'PO-2026-09-001', 'days_ago' => 20, 'notes' => 'Penerimaan Inbound Batch 1 Supplier PT Nusantara'],
                ['wh' => $whSurabaya, 'qty' => 30, 'ref' => 'PO-2026-09-002', 'days_ago' => 18, 'notes' => 'Penerimaan Inbound Hub Timur'],
            ],
            'PRD-RAM-16GB' => [
                ['wh' => $whJakarta, 'qty' => 120, 'ref' => 'PO-2026-09-003', 'days_ago' => 20, 'notes' => 'Penerimaan RAM DDR5 Batch 1'],
                ['wh' => $whSurabaya, 'qty' => 40, 'ref' => 'PO-2026-09-004', 'days_ago' => 17, 'notes' => 'Inbound Hub Surabaya'],
            ],
            'PRD-MON-24IPS' => [
                ['wh' => $whJakarta, 'qty' => 50, 'ref' => 'PO-2026-09-005', 'days_ago' => 15, 'notes' => 'Penerimaan Monitor PT Mega Sukses'],
                ['wh' => $whSurabaya, 'qty' => 20, 'ref' => 'PO-2026-09-006', 'days_ago' => 14, 'notes' => 'Penerimaan Hub SBY'],
            ],
            'PRD-MOU-WL01' => [
                ['wh' => $whJakarta, 'qty' => 200, 'ref' => 'PO-2026-09-007', 'days_ago' => 22, 'notes' => 'Inbound Mouse Wireless'],
                ['wh' => $whSurabaya, 'qty' => 80, 'ref' => 'PO-2026-09-008', 'days_ago' => 19, 'notes' => 'Inbound Mouse Wireless SBY'],
            ],
            'PRD-RTR-WIFI6' => [
                ['wh' => $whJakarta, 'qty' => 45, 'ref' => 'PO-2026-09-009', 'days_ago' => 16, 'notes' => 'Inbound Router WiFi 6 CV Prima'],
                ['wh' => $whSurabaya, 'qty' => 15, 'ref' => 'PO-2026-09-010', 'days_ago' => 15, 'notes' => 'Inbound Router WiFi 6 SBY'],
            ],
            'PRD-LAN-CAT6-305M' => [
                ['wh' => $whJakarta, 'qty' => 30, 'ref' => 'PO-2026-09-011', 'days_ago' => 18, 'notes' => 'Inbound Kabel LAN Cat6 Roll Box'],
                ['wh' => $whSurabaya, 'qty' => 10, 'ref' => 'PO-2026-09-012', 'days_ago' => 16, 'notes' => 'Inbound Kabel LAN SBY'],
            ],
        ];

        foreach ($products as $product) {
            if (isset($initialStocks[$product->sku])) {
                foreach ($initialStocks[$product->sku] as $item) {
                    $date = Carbon::now()->subDays($item['days_ago'])->startOfDay();
                    $inventoryService->stockIn(
                        productId: $product->id,
                        warehouseId: $item['wh']->id,
                        quantity: $item['qty'],
                        type: 'PURCHASE',
                        notes: $item['notes'],
                        userId: $adminId,
                        referenceId: $item['ref'],
                        date: $date
                    );
                }
            }
        }

        // 2. Inter-Warehouse Transfers (Relocations from Jakarta to Surabaya Hub)
        $transfers = [
            ['sku' => 'PRD-SSD-1TB', 'qty' => 15, 'ref' => 'TRF-2026-09-01', 'days_ago' => 12, 'notes' => 'Relokasi stok reguler JKT ke SBY'],
            ['sku' => 'PRD-RAM-16GB', 'qty' => 20, 'ref' => 'TRF-2026-09-02', 'days_ago' => 10, 'notes' => 'Penyelarasan buffer stok hub timur'],
            ['sku' => 'PRD-MOU-WL01', 'qty' => 30, 'ref' => 'TRF-2026-09-03', 'days_ago' => 8, 'notes' => 'Transfer suplai toko cabang Surabaya'],
        ];

        foreach ($transfers as $trf) {
            $product = $products->firstWhere('sku', $trf['sku']);
            if ($product) {
                $date = Carbon::now()->subDays($trf['days_ago'])->startOfDay();
                $inventoryService->transfer(
                    productId: $product->id,
                    fromWarehouseId: $whJakarta->id,
                    toWarehouseId: $whSurabaya->id,
                    quantity: $trf['qty'],
                    notes: $trf['notes'],
                    userId: $adminId,
                    referenceId: $trf['ref'],
                    date: $date
                );
            }
        }

        // 3. Outbound / Sales fulfillment
        $sales = [
            ['sku' => 'PRD-SSD-1TB', 'wh' => $whJakarta, 'qty' => 25, 'ref' => 'INV-2026-09-101', 'days_ago' => 7, 'notes' => 'Penjualan Project Kantor BUMN'],
            ['sku' => 'PRD-SSD-1TB', 'wh' => $whSurabaya, 'qty' => 10, 'ref' => 'INV-2026-09-102', 'days_ago' => 5, 'notes' => 'Penjualan Retail Surabaya Store'],
            ['sku' => 'PRD-RAM-16GB', 'wh' => $whJakarta, 'qty' => 30, 'ref' => 'INV-2026-09-103', 'days_ago' => 6, 'notes' => 'Fulfillment Pesanan Toko Online'],
            ['sku' => 'PRD-MON-24IPS', 'wh' => $whJakarta, 'qty' => 12, 'ref' => 'INV-2026-09-104', 'days_ago' => 4, 'notes' => 'Penjualan Instansi Lab Komputer'],
            ['sku' => 'PRD-MOU-WL01', 'wh' => $whJakarta, 'qty' => 40, 'ref' => 'INV-2026-09-105', 'days_ago' => 3, 'notes' => 'Penjualan Grosir Reseller'],
            ['sku' => 'PRD-RTR-WIFI6', 'wh' => $whJakarta, 'qty' => 8, 'ref' => 'INV-2026-09-106', 'days_ago' => 2, 'notes' => 'Instalasi Jaringan Hotel'],
        ];

        foreach ($sales as $sale) {
            $product = $products->firstWhere('sku', $sale['sku']);
            if ($product) {
                $date = Carbon::now()->subDays($sale['days_ago'])->startOfDay();
                $inventoryService->stockOut(
                    productId: $product->id,
                    warehouseId: $sale['wh']->id,
                    quantity: $sale['qty'],
                    type: 'SALE',
                    notes: $sale['notes'],
                    userId: $adminId,
                    referenceId: $sale['ref'],
                    date: $date
                );
            }
        }

        // 4. Stock Opname / Physical Adjustment
        $adjustments = [
            ['sku' => 'PRD-MOU-WL01', 'wh' => $whSurabaya, 'actual_qty' => 108, 'ref' => 'BA-OPN-2026-09', 'days_ago' => 1, 'notes' => 'Penyesuaian hasil stock opname bulanan'],
        ];

        foreach ($adjustments as $adj) {
            $product = $products->firstWhere('sku', $adj['sku']);
            if ($product) {
                $date = Carbon::now()->subDays($adj['days_ago'])->startOfDay();
                $inventoryService->adjust(
                    productId: $product->id,
                    warehouseId: $adj['wh']->id,
                    actualQuantity: $adj['actual_qty'],
                    reason: $adj['notes'],
                    userId: $adminId,
                    referenceId: $adj['ref'],
                    date: $date
                );
            }
        }
    }
}
