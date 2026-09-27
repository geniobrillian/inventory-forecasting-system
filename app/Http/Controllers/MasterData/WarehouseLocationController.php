<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\WarehouseLocationRequest;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;

class WarehouseLocationController extends Controller
{
    public function store(WarehouseLocationRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $warehouse->locations()->create($validated);

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Sub-lokasi gudang berhasil ditambahkan.');
    }

    public function update(WarehouseLocationRequest $request, Warehouse $warehouse, WarehouseLocation $location): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $location->is_active;

        $location->update($validated);

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Sub-lokasi gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse, WarehouseLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Sub-lokasi berhasil dihapus.');
    }

    public function toggleStatus(Warehouse $warehouse, WarehouseLocation $location): RedirectResponse
    {
        $location->update(['is_active' => !$location->is_active]);

        $statusText = $location->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('warehouses.show', $warehouse)->with('success', "Status lokasi '{$location->name}' berhasil {$statusText}.");
    }
}
