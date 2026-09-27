<?php

namespace App\Http\Controllers\Inventory;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Http\Requests\Inventory\StockInRequest;
use App\Http\Requests\Inventory\StockOutRequest;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Tampilan Ringkasan Stok (Stock Overview).
     */
    public function overview(Request $request): View
    {
        $query = Product::with(['category', 'unit', 'inventoryStocks.warehouse'])
            ->where('is_active', true);

        // Search Product
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter Warehouse
        $warehouseFilter = $request->input('warehouse_id');
        if ($warehouseFilter) {
            $query->whereHas('inventoryStocks', function ($q) use ($warehouseFilter) {
                $q->where('warehouse_id', $warehouseFilter);
            });
        }

        $products = $query->paginate(10)->withQueryString();

        // Calculate KPI Stats
        $allStocks = InventoryStock::with('product')->get();
        $totalValuation = $allStocks->sum(fn ($s) => $s->quantity * ($s->product?->purchase_price ?? 0));
        $totalUnits = $allStocks->sum('quantity');

        $lowStockCount = Product::where('is_active', true)->get()->filter(function ($prod) {
            return $prod->total_stock > 0 && $prod->total_stock <= $prod->minimum_stock;
        })->count();

        $outOfStockCount = Product::where('is_active', true)->get()->filter(function ($prod) {
            return $prod->total_stock <= 0;
        })->count();

        $stats = [
            'total_units' => $totalUnits,
            'total_valuation' => $totalValuation,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
        ];

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.overview', compact('products', 'stats', 'categories', 'warehouses'));
    }

    /**
     * Form Penerimaan Barang (Stock In).
     */
    public function stockInForm(): View
    {
        $products = Product::where('is_active', true)->with('unit')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock-in', compact('products', 'warehouses'));
    }

    public function processStockIn(StockInRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $date = !empty($validated['transaction_date']) ? Carbon::parse($validated['transaction_date']) : Carbon::now();

        $this->inventoryService->stockIn(
            productId: (int) $validated['product_id'],
            warehouseId: (int) $validated['warehouse_id'],
            quantity: (int) $validated['quantity'],
            type: $validated['transaction_type'],
            notes: $validated['notes'] ?? null,
            userId: auth()->id(),
            date: $date
        );

        return redirect()->route('inventory.overview')->with('success', 'Transaksi Stock In berhasil dicatat dan saldo stok diperbarui.');
    }

    /**
     * Form Pengeluaran Barang (Stock Out).
     */
    public function stockOutForm(): View
    {
        $products = Product::where('is_active', true)->with(['unit', 'inventoryStocks'])->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock-out', compact('products', 'warehouses'));
    }

    public function processStockOut(StockOutRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $date = !empty($validated['transaction_date']) ? Carbon::parse($validated['transaction_date']) : Carbon::now();

        try {
            $this->inventoryService->stockOut(
                productId: (int) $validated['product_id'],
                warehouseId: (int) $validated['warehouse_id'],
                quantity: (int) $validated['quantity'],
                type: $validated['transaction_type'],
                notes: $validated['notes'] ?? null,
                userId: auth()->id(),
                date: $date
            );

            return redirect()->route('inventory.overview')->with('success', 'Transaksi Stock Out berhasil dicatat dan saldo stok dipotong.');
        } catch (InsufficientStockException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()])->with('error', $e->getMessage());
        }
    }

    /**
     * Form Transfer Stok Antar Gudang.
     */
    public function transferForm(): View
    {
        $products = Product::where('is_active', true)->with(['unit', 'inventoryStocks'])->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.transfer', compact('products', 'warehouses'));
    }

    public function processTransfer(StockTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $date = !empty($validated['transaction_date']) ? Carbon::parse($validated['transaction_date']) : Carbon::now();

        try {
            $this->inventoryService->transfer(
                productId: (int) $validated['product_id'],
                fromWarehouseId: (int) $validated['from_warehouse_id'],
                toWarehouseId: (int) $validated['to_warehouse_id'],
                quantity: (int) $validated['quantity'],
                notes: $validated['notes'] ?? null,
                userId: auth()->id(),
                date: $date
            );

            return redirect()->route('inventory.overview')->with('success', 'Transfer stok antar-gudang berhasil diproses secara atomik.');
        } catch (InsufficientStockException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()])->with('error', $e->getMessage());
        }
    }

    /**
     * Form Penyesuaian Stok Fisik / Stock Opname.
     */
    public function adjustmentForm(): View
    {
        $products = Product::where('is_active', true)->with(['unit', 'inventoryStocks'])->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.adjustment', compact('products', 'warehouses'));
    }

    public function processAdjustment(StockAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $date = !empty($validated['transaction_date']) ? Carbon::parse($validated['transaction_date']) : Carbon::now();

        $transaction = $this->inventoryService->adjust(
            productId: (int) $validated['product_id'],
            warehouseId: (int) $validated['warehouse_id'],
            actualQuantity: (int) $validated['actual_quantity'],
            reason: $validated['notes'],
            userId: auth()->id(),
            date: $date
        );

        $msg = $transaction
            ? "Penyesuaian stok berhasil disimpan (Tipe: {$transaction->transaction_type}, Selisih: {$transaction->quantity})."
            : "Stok fisik sudah sesuai dengan pencatatan sistem, tidak ada perubahan saldo.";

        return redirect()->route('inventory.overview')->with('success', $msg);
    }

    /**
     * Endpoint AJAX untuk cek stok produk di gudang secara dinamis.
     */
    public function getStockAjax(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $warehouseId = (int) $request->input('warehouse_id');

        if (!$productId || !$warehouseId) {
            return response()->json([
                'success' => false,
                'quantity' => 0,
                'reserved' => 0,
                'available' => 0,
            ]);
        }

        $stock = InventoryStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $product = Product::with('unit')->find($productId);

        return response()->json([
            'success' => true,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $stock ? $stock->quantity : 0,
            'reserved' => $stock ? $stock->reserved_quantity : 0,
            'available' => $stock ? $stock->available_quantity : 0,
            'unit' => $product?->unit?->code ?? 'Unit',
        ]);
    }
}
