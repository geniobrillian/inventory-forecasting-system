<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Warehouse::withCount('locations');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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

        $warehouses = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('is_active', true)->count(),
            'inactive' => Warehouse::where('is_active', false)->count(),
        ];

        return view('warehouses.index', compact('warehouses', 'stats'));
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $warehouse = Warehouse::create($validated);

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Gudang baru berhasil ditambahkan.');
    }

    public function show(Warehouse $warehouse): View
    {
        $locations = $warehouse->locations()->latest()->paginate(15);
        $locationStats = [
            'total' => $warehouse->locations()->count(),
            'racks' => $warehouse->locations()->where('type', 'RACK')->count(),
            'zones' => $warehouse->locations()->where('type', 'ZONE')->count(),
            'bins' => $warehouse->locations()->where('type', 'BIN')->count(),
        ];

        return view('warehouses.show', compact('warehouse', 'locations', 'locationStats'));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $warehouse->is_active;

        $warehouse->update($validated);

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Informasi gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $locationsCount = $warehouse->locations()->count();
        if ($locationsCount > 0) {
            return redirect()->route('warehouses.index')->with('error', "Gudang '{$warehouse->name}' memiliki {$locationsCount} sub-lokasi. Hapus atau pindahkan sub-lokasi terlebih dahulu sebelum menghapus gudang.");
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus (soft delete).');
    }

    public function toggleStatus(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update(['is_active' => !$warehouse->is_active]);

        $statusText = $warehouse->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status gudang '{$warehouse->name}' berhasil {$statusText}.");
    }
}
