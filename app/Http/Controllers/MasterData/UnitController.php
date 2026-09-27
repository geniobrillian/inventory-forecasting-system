<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Unit::withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
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

        $units = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Unit::count(),
            'active' => Unit::where('is_active', true)->count(),
            'inactive' => Unit::where('is_active', false)->count(),
        ];

        return view('units.index', compact('units', 'stats'));
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Satuan unit baru berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $unit->is_active;

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Satuan unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $productCount = $unit->products()->count();
        if ($productCount > 0) {
            return redirect()->route('units.index')->with('error', "Satuan '{$unit->name}' tidak dapat dihapus karena sedang digunakan oleh {$productCount} produk. Nonaktifkan satuan ini sebagai alternatif.");
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan unit berhasil dihapus (soft delete).');
    }

    public function toggleStatus(Unit $unit): RedirectResponse
    {
        $unit->update(['is_active' => !$unit->is_active]);

        $statusText = $unit->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status satuan unit '{$unit->name}' berhasil {$statusText}.");
    }
}
