<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'unit', 'supplier']);

        // Search by SKU or Name
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

        // Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        // Filter Forecast Method
        if ($request->filled('forecast_method')) {
            $query->where('forecast_method', $request->input('forecast_method'));
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['sku', 'name', 'purchase_price', 'selling_price', 'minimum_stock', 'lead_time_days', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'avg_margin' => Product::where('purchase_price', '>', 0)
                ->selectRaw('AVG(((selling_price - purchase_price) / selling_price) * 100) as avg_margin')
                ->value('avg_margin') ?? 0,
        ];

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'stats', 'categories', 'suppliers'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'units', 'suppliers'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['sku'] = strtoupper($validated['sku']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $product = Product::create($validated);

        return redirect()->route('products.show', $product)->with('success', 'Produk baru berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'unit', 'supplier']);

        $profitMargin = $product->selling_price > 0
            ? (($product->selling_price - $product->purchase_price) / $product->selling_price) * 100
            : 0;

        return view('products.show', compact('product', 'profitMargin'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'units', 'suppliers'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $validated['sku'] = strtoupper($validated['sku']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $product->is_active;

        $product->update($validated);

        return redirect()->route('products.show', $product)->with('success', 'Informasi produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus (soft delete).');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update(['is_active' => !$product->is_active]);

        $statusText = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status produk '{$product->name}' berhasil {$statusText}.");
    }
}
