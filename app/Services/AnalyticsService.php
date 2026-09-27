<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Menghitung Ringkasan KPI Utama Inventaris.
     */
    public function getKpiMetrics(): array
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalStockQty = (int) InventoryStock::sum('quantity');

        // Total Inventory Valuation (IDR) = sum(stocks.quantity * products.purchase_price)
        $totalInventoryValue = (float) InventoryStock::join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->selectRaw('SUM(inventory_stocks.quantity * products.purchase_price) as total_val')
            ->value('total_val') ?? 0;

        // Total Sales in last 30 days
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
        $totalSalesValue30d = (float) Sale::where('status', Sale::STATUS_COMPLETED)
            ->where('sale_date', '>=', $thirtyDaysAgo)
            ->sum('total');

        // Stock Health Breakdown
        $health = $this->getStockHealthCounts();

        // Calculate 30-day COGS (Cost of Goods Sold)
        $cogs30d = (float) InventoryTransaction::join('products', 'inventory_transactions.product_id', '=', 'products.id')
            ->where('inventory_transactions.transaction_type', InventoryTransaction::TYPE_SALE)
            ->where('inventory_transactions.transaction_date', '>=', $thirtyDaysAgo)
            ->selectRaw('SUM(inventory_transactions.quantity * products.purchase_price) as cogs')
            ->value('cogs') ?? 0;

        // Inventory Turnover Ratio = COGS (30d) / Average Inventory Value
        // Annualized ratio = (COGS 30d / Average Inventory) * (365 / 30)
        $turnoverRatio = $totalInventoryValue > 0
            ? round(($cogs30d / $totalInventoryValue) * (365 / 30), 2)
            : 0;

        return [
            'total_products' => $totalProducts,
            'total_stock_quantity' => $totalStockQty,
            'total_inventory_value' => $totalInventoryValue,
            'total_sales_value_30d' => $totalSalesValue30d,
            'cogs_30d' => $cogs30d,
            'turnover_ratio' => $turnoverRatio,
            'out_of_stock_count' => $health['out_of_stock'],
            'critical_stock_count' => $health['critical'],
            'low_stock_count' => $health['low_stock'],
            'healthy_stock_count' => $health['healthy'],
        ];
    }

    /**
     * Menghitung klasifikasi kesehatan stok per produk.
     */
    public function getStockHealthCounts(): array
    {
        $products = Product::where('is_active', true)
            ->withSum('stocks', 'quantity')
            ->get();

        $outOfStock = 0;
        $critical = 0;
        $lowStock = 0;
        $healthy = 0;

        foreach ($products as $prod) {
            $qty = (int) ($prod->stocks_sum_quantity ?? 0);
            $minStock = (int) ($prod->minimum_stock ?? 0);

            if ($qty <= 0) {
                $outOfStock++;
            } elseif ($qty <= max(1, (int) round($minStock * 0.4))) {
                $critical++;
            } elseif ($qty <= $minStock) {
                $lowStock++;
            } else {
                $healthy++;
            }
        }

        return [
            'out_of_stock' => $outOfStock,
            'critical' => $critical,
            'low_stock' => $lowStock,
            'healthy' => $healthy,
            'total' => $products->count(),
        ];
    }

    /**
     * Analisis Kecepatan Perputaran Barang (Fast Moving, Slow Moving, Dead Stock).
     */
    public function getVelocityMetrics(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
        $ninetyDaysAgo = Carbon::now()->subDays(90)->startOfDay();

        // 1. Demand in last 30 days per product
        $demand30d = InventoryTransaction::where('transaction_type', InventoryTransaction::TYPE_SALE)
            ->where('transaction_date', '>=', $thirtyDaysAgo)
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id')
            ->toArray();

        // 2. Last sale date per product
        $lastSaleDates = InventoryTransaction::where('transaction_type', InventoryTransaction::TYPE_SALE)
            ->select('product_id', DB::raw('MAX(transaction_date) as last_sale_date'))
            ->groupBy('product_id')
            ->pluck('last_sale_date', 'product_id')
            ->toArray();

        $products = Product::where('is_active', true)->with(['unit', 'category'])->get();

        $fastMoving = [];
        $slowMoving = [];
        $deadStock = [];

        foreach ($products as $prod) {
            $sold30 = $demand30d[$prod->id] ?? 0;
            $lastSale = isset($lastSaleDates[$prod->id]) ? Carbon::parse($lastSaleDates[$prod->id]) : null;

            $prod->sold_30d = $sold30;
            $prod->last_sale_date = $lastSale;

            if ($sold30 >= 20) {
                $fastMoving[] = $prod;
            } elseif ($sold30 > 0) {
                $slowMoving[] = $prod;
            } else {
                // Check if never sold or no sales in 90 days
                if (!$lastSale || $lastSale->lessThan($ninetyDaysAgo)) {
                    $prod->days_inactive = $lastSale ? $lastSale->diffInDays(now()) : $prod->created_at->diffInDays(now());
                    $deadStock[] = $prod;
                } else {
                    $slowMoving[] = $prod;
                }
            }
        }

        // Sort fast moving by highest sales
        usort($fastMoving, fn ($a, $b) => $b->sold_30d <=> $a->sold_30d);

        return [
            'fast_moving' => collect($fastMoving)->take(10),
            'fast_moving_count' => count($fastMoving),
            'slow_moving_count' => count($slowMoving),
            'dead_stock' => collect($deadStock)->sortByDesc('days_inactive')->take(10),
            'dead_stock_count' => count($deadStock),
        ];
    }

    /**
     * Data Grafik Tren Permintaan Harian (Demand Trend).
     */
    public function getDemandTrendChartData(int $days = 14): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $rawSales = InventoryTransaction::where('transaction_type', InventoryTransaction::TYPE_SALE)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(transaction_date) as t_date'), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('t_date')
            ->pluck('total_qty', 't_date')
            ->toArray();

        $labels = [];
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
            $dateLabel = Carbon::parse($date)->format('d M');

            $labels[] = $dateLabel;
            $series[] = (int) ($rawSales[$date] ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'Permintaan Terjual (Unit)',
                    'data' => $series,
                ]
            ],
        ];
    }

    /**
     * Data Grafik Mutasi Barang Masuk vs Keluar per Bulan.
     */
    public function getStockMovementChartData(int $months = 6): array
    {
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $rawIn = InventoryTransaction::whereIn('transaction_type', [
                InventoryTransaction::TYPE_PURCHASE,
                InventoryTransaction::TYPE_RETURN_IN,
                InventoryTransaction::TYPE_ADJUSTMENT_IN,
                InventoryTransaction::TYPE_TRANSFER_IN,
                'INITIAL'
            ])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select(DB::raw("strftime('%Y-%m', transaction_date) as t_month"), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('t_month')
            ->pluck('total_qty', 't_month')
            ->toArray();

        $rawOut = InventoryTransaction::whereIn('transaction_type', [
                InventoryTransaction::TYPE_SALE,
                InventoryTransaction::TYPE_RETURN_OUT,
                InventoryTransaction::TYPE_ADJUSTMENT_OUT,
                InventoryTransaction::TYPE_TRANSFER_OUT
            ])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select(DB::raw("strftime('%Y-%m', transaction_date) as t_month"), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('t_month')
            ->pluck('total_qty', 't_month')
            ->toArray();

        $labels = [];
        $inSeries = [];
        $outSeries = [];

        for ($i = 0; $i < $months; $i++) {
            $m = Carbon::now()->subMonths($months - 1 - $i);
            $monthKey = $m->format('Y-m');
            $monthLabel = $m->format('M Y');

            $labels[] = $monthLabel;
            $inSeries[] = (int) ($rawIn[$monthKey] ?? 0);
            $outSeries[] = (int) ($rawOut[$monthKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'Barang Masuk (Stock In)',
                    'data' => $inSeries,
                ],
                [
                    'name' => 'Barang Keluar (Stock Out)',
                    'data' => $outSeries,
                ]
            ],
        ];
    }

    /**
     * Data Grafik Distribusi Stok dan Valuasi antar Gudang.
     */
    public function getWarehouseDistributionChartData(): array
    {
        $warehouses = Warehouse::where('is_active', true)
            ->with(['stocks.product'])
            ->get();

        $labels = [];
        $quantities = [];
        $values = [];

        foreach ($warehouses as $wh) {
            $labels[] = $wh->name;
            $qty = (int) $wh->stocks->sum('quantity');
            $val = (float) $wh->stocks->reduce(function ($acc, $stock) {
                return $acc + ($stock->quantity * ($stock->product?->purchase_price ?? 0));
            }, 0);

            $quantities[] = $qty;
            $values[] = $val;
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities,
            'values' => $values,
        ];
    }

    /**
     * 10 Transaksi Mutasi Terakhir.
     */
    public function getRecentTransactions(int $limit = 8)
    {
        return InventoryTransaction::with(['product.unit', 'warehouse', 'user'])
            ->latest('transaction_date')
            ->latest('id')
            ->take($limit)
            ->get();
    }

    /**
     * Top 5 Produk Paling Laris (Top Selling) dalam 30 Hari Terakhir.
     */
    public function getTopSellingProducts(int $limit = 5)
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();

        return InventoryTransaction::join('products', 'inventory_transactions.product_id', '=', 'products.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->where('inventory_transactions.transaction_type', InventoryTransaction::TYPE_SALE)
            ->where('inventory_transactions.transaction_date', '>=', $thirtyDaysAgo)
            ->select(
                'products.id',
                'products.sku',
                'products.name',
                'units.symbol as unit_symbol',
                'products.selling_price',
                DB::raw('SUM(inventory_transactions.quantity) as total_sold'),
                DB::raw('SUM(inventory_transactions.quantity * products.selling_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.sku', 'products.name', 'units.symbol', 'products.selling_price')
            ->orderByDesc('total_sold')
            ->take($limit)
            ->get();
    }
}
