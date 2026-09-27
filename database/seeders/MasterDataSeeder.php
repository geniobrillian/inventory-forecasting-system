<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $categories = [
            [
                'name' => 'Komponen & Hardware',
                'slug' => 'komponen-hardware',
                'description' => 'Komponen internal komputer seperti processor, motherboard, RAM, dan SSD.',
                'is_active' => true,
            ],
            [
                'name' => 'Peripheral & Aksesoris',
                'slug' => 'peripheral-aksesoris',
                'description' => 'Perangkat input/output seperti monitor, keyboard, mouse, dan headset.',
                'is_active' => true,
            ],
            [
                'name' => 'Jaringan & Server',
                'slug' => 'jaringan-server',
                'description' => 'Peralatan infrastruktur jaringan, router, switch hub, dan kabel fiber optic.',
                'is_active' => true,
            ],
            [
                'name' => 'Peralatan & ATK',
                'slug' => 'peralatan-atk',
                'description' => 'Perlengkapan operasional kantor dan kertas cetak.',
                'is_active' => true,
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Units
        $units = [
            ['code' => 'PCS', 'name' => 'Pieces / Buah', 'symbol' => 'pcs', 'description' => 'Satuan hitung per item individual.', 'is_active' => true],
            ['code' => 'BOX', 'name' => 'Box / Kotak', 'symbol' => 'bx', 'description' => 'Satuan kemasan kotak atau kardus.', 'is_active' => true],
            ['code' => 'UNIT', 'name' => 'Unit Lengkap', 'symbol' => 'unit', 'description' => 'Satuan set peralatan utuh.', 'is_active' => true],
            ['code' => 'PACK', 'name' => 'Pack / Bungkus', 'symbol' => 'pk', 'description' => 'Satuan kemasan bungkus isi beberapa pcs.', 'is_active' => true],
            ['code' => 'ROLL', 'name' => 'Roll / Gulungan', 'symbol' => 'rl', 'description' => 'Satuan panjang seperti kabel dan pita.', 'is_active' => true],
        ];

        $unitModels = [];
        foreach ($units as $u) {
            $unitModels[$u['code']] = Unit::firstOrCreate(['code' => $u['code']], $u);
        }

        // 3. Suppliers
        $suppliers = [
            [
                'code' => 'SUP-NTD01',
                'name' => 'PT Nusantara Teknologi Distribusi',
                'contact_person' => 'Budi Santoso',
                'phone' => '081211112222',
                'email' => 'sales@nusantaratekno.co.id',
                'address' => 'Kawasan Industri Pulogadung Blok B No. 12, Jakarta Timur',
                'default_lead_time_days' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'SUP-MSE02',
                'name' => 'PT Mega Sukses Elektronik',
                'contact_person' => 'Dewi Lestari',
                'phone' => '081233334444',
                'email' => 'order@megasukses.com',
                'address' => 'Komplek Pergudangan Margomulyo Permai Kav. 8, Surabaya',
                'default_lead_time_days' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'SUP-PSA03',
                'name' => 'CV Prima Sentosa Abadi',
                'contact_person' => 'Hendro Wijaya',
                'phone' => '081255556666',
                'email' => 'info@primasentosa.id',
                'address' => 'Jl. Soekarno Hatta No. 450, Bandung',
                'default_lead_time_days' => 10,
                'is_active' => true,
            ],
        ];

        $supplierModels = [];
        foreach ($suppliers as $s) {
            $supplierModels[$s['code']] = Supplier::firstOrCreate(['code' => $s['code']], $s);
        }

        // 4. Warehouses & Warehouse Locations
        $warehouses = [
            [
                'code' => 'WH-JKT01',
                'name' => 'Gudang Pusat Jakarta',
                'address' => 'Jl. Raya Cakung Cilincing No. 88, Jakarta Timur',
                'description' => 'Fasilitas gudang utama penyimpanan stok nasional dan staging area.',
                'is_active' => true,
                'locations' => [
                    ['code' => 'RAK-A01', 'name' => 'Rak Komponen A1', 'type' => 'RACK', 'description' => 'Tingkat 1-4 untuk CPU & RAM'],
                    ['code' => 'RAK-A02', 'name' => 'Rak Komponen A2', 'type' => 'RACK', 'description' => 'Tingkat 1-4 untuk Motherboard & SSD'],
                    ['code' => 'RAK-B01', 'name' => 'Rak Peripheral B1', 'type' => 'RACK', 'description' => 'Area monitor dan aksesoris besar'],
                    ['code' => 'ZONA-TRANSIT', 'name' => 'Zona Staging & Transit Inbound', 'type' => 'ZONE', 'description' => 'Area inspeksi penerimaan barang supplier'],
                    ['code' => 'BIN-C01', 'name' => 'Bin Kabel & Aksesoris Kecil', 'type' => 'BIN', 'description' => 'Penyimpanan barang kecil'],
                ],
            ],
            [
                'code' => 'WH-SBY02',
                'name' => 'Gudang Hub Surabaya',
                'address' => 'Jl. Rungkut Industri Raya No. 45, Surabaya',
                'description' => 'Gudang distribusi regional Jawa Timur dan Indonesia Timur.',
                'is_active' => true,
                'locations' => [
                    ['code' => 'RAK-S01', 'name' => 'Rak Hub Timur S1', 'type' => 'RACK', 'description' => 'Stok cepat perputaran'],
                    ['code' => 'RAK-S02', 'name' => 'Rak Hub Timur S2', 'type' => 'RACK', 'description' => 'Stok buffer cadangan'],
                    ['code' => 'ZONA-PICKING', 'name' => 'Zona Fast Picking', 'type' => 'ZONE', 'description' => 'Area sortir pengiriman kurir'],
                ],
            ],
        ];

        foreach ($warehouses as $whData) {
            $locations = $whData['locations'];
            unset($whData['locations']);

            $wh = Warehouse::firstOrCreate(['code' => $whData['code']], $whData);

            foreach ($locations as $loc) {
                WarehouseLocation::firstOrCreate([
                    'warehouse_id' => $wh->id,
                    'code' => $loc['code'],
                ], array_merge($loc, ['warehouse_id' => $wh->id, 'is_active' => true]));
            }
        }

        // 5. Products
        $products = [
            [
                'sku' => 'PRD-SSD-1TB',
                'name' => 'NVMe SSD 1TB PCIe Gen 4.0',
                'category_id' => $categoryModels['komponen-hardware']->id,
                'unit_id' => $unitModels['PCS']->id,
                'supplier_id' => $supplierModels['SUP-NTD01']->id,
                'purchase_price' => 850000,
                'selling_price' => 1250000,
                'minimum_stock' => 20,
                'lead_time_days' => 7,
                'forecast_method' => 'MOVING_AVERAGE',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-RAM-16GB',
                'name' => 'DDR5 RAM 16GB 5600MHz RGB',
                'category_id' => $categoryModels['komponen-hardware']->id,
                'unit_id' => $unitModels['PCS']->id,
                'supplier_id' => $supplierModels['SUP-NTD01']->id,
                'purchase_price' => 600000,
                'selling_price' => 850000,
                'minimum_stock' => 25,
                'lead_time_days' => 7,
                'forecast_method' => 'EXPONENTIAL_SMOOTHING',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-MON-24IPS',
                'name' => 'Monitor LED 24 Inch Full HD IPS 100Hz',
                'category_id' => $categoryModels['peripheral-aksesoris']->id,
                'unit_id' => $unitModels['UNIT']->id,
                'supplier_id' => $supplierModels['SUP-MSE02']->id,
                'purchase_price' => 1200000,
                'selling_price' => 1650000,
                'minimum_stock' => 15,
                'lead_time_days' => 5,
                'forecast_method' => 'MOVING_AVERAGE',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-MOU-WL01',
                'name' => 'Wireless Ergonomic Optical Mouse',
                'category_id' => $categoryModels['peripheral-aksesoris']->id,
                'unit_id' => $unitModels['PCS']->id,
                'supplier_id' => $supplierModels['SUP-MSE02']->id,
                'purchase_price' => 95000,
                'selling_price' => 150000,
                'minimum_stock' => 50,
                'lead_time_days' => 5,
                'forecast_method' => 'MOVING_AVERAGE',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-RTR-WIFI6',
                'name' => 'AX3000 Dual Band Gigabit Wi-Fi 6 Router',
                'category_id' => $categoryModels['jaringan-server']->id,
                'unit_id' => $unitModels['UNIT']->id,
                'supplier_id' => $supplierModels['SUP-PSA03']->id,
                'purchase_price' => 450000,
                'selling_price' => 699000,
                'minimum_stock' => 10,
                'lead_time_days' => 10,
                'forecast_method' => 'EXPONENTIAL_SMOOTHING',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-LAN-CAT6-305M',
                'name' => 'Kabel UTP Cat6 UTP 305 Meter Box',
                'category_id' => $categoryModels['jaringan-server']->id,
                'unit_id' => $unitModels['BOX']->id,
                'supplier_id' => $supplierModels['SUP-PSA03']->id,
                'purchase_price' => 980000,
                'selling_price' => 1400000,
                'minimum_stock' => 8,
                'lead_time_days' => 10,
                'forecast_method' => 'MOVING_AVERAGE',
                'is_active' => true,
            ],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['sku' => $p['sku']], $p);
        }
    }
}
