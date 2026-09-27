<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier baru berhasil didaftarkan.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['products' => function ($q) {
            $q->with(['category', 'unit'])->latest()->take(10);
        }]);

        $productsCount = $supplier->products()->count();

        return view('suppliers.show', compact('supplier', 'productsCount'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $supplier->is_active;

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Informasi supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $productCount = $supplier->products()->count();
        if ($productCount > 0) {
            return redirect()->route('suppliers.index')->with('error', "Supplier '{$supplier->name}' tidak dapat dihapus karena menyuplai {$productCount} produk. Nonaktifkan supplier ini sebagai alternatif.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus (soft delete).');
    }

    public function toggleStatus(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['is_active' => !$supplier->is_active]);

        $statusText = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status supplier '{$supplier->name}' berhasil {$statusText}.");
    }
}
