<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockCardController extends Controller
{
    /**
     * Halaman Kartu Stok (Stock Card) real-time ledger.
     */
    public function index(Request $request): View
    {
        $products = Product::where('is_active', true)->with('unit')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        // Selected Product (null if not explicitly chosen)
        $selectedProductId = $request->filled('product_id') ? (int) $request->input('product_id') : null;
        $selectedWarehouseId = $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null;

        $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        $selectedProduct = $selectedProductId ? Product::with(['unit', 'category', 'supplier'])->find($selectedProductId) : null;
        $selectedWarehouse = $selectedWarehouseId ? Warehouse::find($selectedWarehouseId) : null;

        $transactions = collect();
        $initialBalance = 0;
        $totalIn = 0;
        $totalOut = 0;
        $finalBalance = 0;

        if ($selectedProduct) {
            // 1. Calculate Initial Balance before startDate
            $initialQuery = InventoryTransaction::where('product_id', $selectedProduct->id)
                ->where('transaction_date', '<', $startDate);

            if ($selectedWarehouseId) {
                $initialQuery->where('warehouse_id', $selectedWarehouseId);
            }

            $beforeTransactions = $initialQuery->get();
            foreach ($beforeTransactions as $tx) {
                if ($tx->isStockIncrement()) {
                    $initialBalance += $tx->quantity;
                } else {
                    $initialBalance -= $tx->quantity;
                }
            }

            // 2. Fetch Transactions in Date Range
            $ledgerQuery = InventoryTransaction::with(['warehouse', 'user'])
                ->where('product_id', $selectedProduct->id)
                ->whereBetween('transaction_date', [$startDate, $endDate]);

            if ($selectedWarehouseId) {
                $ledgerQuery->where('warehouse_id', $selectedWarehouseId);
            }

            if ($request->filled('type')) {
                $ledgerQuery->where('transaction_type', $request->input('type'));
            }

            $rawTransactions = $ledgerQuery->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

            // 3. Compute running balance row by row
            $runningBalance = $initialBalance;
            $transactions = $rawTransactions->map(function ($tx) use (&$runningBalance, &$totalIn, &$totalOut) {
                $isIn = $tx->isStockIncrement();
                $qtyIn = $isIn ? $tx->quantity : 0;
                $qtyOut = !$isIn ? $tx->quantity : 0;

                $totalIn += $qtyIn;
                $totalOut += $qtyOut;

                if ($isIn) {
                    $runningBalance += $qtyIn;
                } else {
                    $runningBalance -= $qtyOut;
                }

                $tx->qty_in = $qtyIn;
                $tx->qty_out = $qtyOut;
                $tx->balance = $runningBalance;

                return $tx;
            });

            $finalBalance = $runningBalance;
        }

        // Current real stock in DB for verification
        $currentStockQuery = InventoryStock::where('product_id', $selectedProductId);
        if ($selectedWarehouseId) {
            $currentStockQuery->where('warehouse_id', $selectedWarehouseId);
        }
        $currentRealStock = (int) $currentStockQuery->sum('quantity');

        return view('inventory.stock-card', [
            'products' => $products,
            'warehouses' => $warehouses,
            'selectedProduct' => $selectedProduct,
            'selectedProductId' => $selectedProductId,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedWarehouseId' => $selectedWarehouseId,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'transactions' => $transactions,
            'initialBalance' => $initialBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'finalBalance' => $finalBalance,
            'currentRealStock' => $currentRealStock,
        ]);
    }
}
