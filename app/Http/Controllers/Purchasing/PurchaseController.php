<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\PurchaseRequest;
use App\Http\Requests\Purchasing\ReceivePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $purchaseService
    ) {}

    /**
     * Daftar Purchase Orders dengan filter dan KPI Metrik.
     */
    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'warehouse', 'creator', 'items.product']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('items.product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->input('date_to'));
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();

        // KPI Stats
        $stats = [
            'total_po' => Purchase::count(),
            'total_spent' => (float) Purchase::whereIn('status', [Purchase::STATUS_ORDERED, Purchase::STATUS_PARTIALLY_RECEIVED, Purchase::STATUS_RECEIVED])->sum('total'),
            'pending_ordered' => Purchase::where('status', Purchase::STATUS_ORDERED)->count(),
            'partial_received' => Purchase::where('status', Purchase::STATUS_PARTIALLY_RECEIVED)->count(),
            'completed' => Purchase::where('status', Purchase::STATUS_RECEIVED)->count(),
        ];

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('purchasing.index', compact('purchases', 'stats', 'suppliers', 'warehouses'));
    }

    /**
     * Form Tambah Purchase Order Baru.
     */
    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with(['unit', 'supplier'])->orderBy('name')->get();
        $autoPoNumber = $this->purchaseService->generatePurchaseNumber();

        return view('purchasing.create', compact('suppliers', 'warehouses', 'products', 'autoPoNumber'));
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $purchase = $this->purchaseService->createPurchase(
            data: $validated,
            items: $validated['items'],
            userId: auth()->id() ?? 1
        );

        return redirect()->route('purchasing.show', $purchase)
            ->with('success', "Purchase Order {$purchase->purchase_number} berhasil dibuat.");
    }

    /**
     * Detail Purchase Order.
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['items.product.unit', 'supplier', 'warehouse', 'creator']);

        return view('purchasing.show', compact('purchase'));
    }

    /**
     * Form Ubah Purchase Order (Draft / Ordered).
     */
    public function edit(Purchase $purchase): View|RedirectResponse
    {
        if (!$purchase->canEdit()) {
            return redirect()->route('purchasing.show', $purchase)
                ->with('error', "Purchase Order berstatus {$purchase->status} tidak dapat diubah.");
        }

        $purchase->load(['items.product.unit', 'supplier', 'warehouse']);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with(['unit', 'supplier'])->orderBy('name')->get();

        return view('purchasing.edit', compact('purchase', 'suppliers', 'warehouses', 'products'));
    }

    public function update(PurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->purchaseService->updatePurchase(
                purchase: $purchase,
                data: $validated,
                items: $validated['items']
            );

            return redirect()->route('purchasing.show', $purchase)
                ->with('success', "Purchase Order {$purchase->purchase_number} berhasil diperbarui.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Mengajukan PO ke supplier (Status DRAFT -> ORDERED).
     */
    public function order(Purchase $purchase): RedirectResponse
    {
        try {
            $this->purchaseService->orderPurchase($purchase);
            return redirect()->route('purchasing.show', $purchase)
                ->with('success', "Purchase Order {$purchase->purchase_number} telah diajukan pemesanan ke Supplier.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Form Penerimaan Barang Fisik (Goods Receiving).
     */
    public function receiveForm(Purchase $purchase): View|RedirectResponse
    {
        if (!$purchase->canReceive()) {
            return redirect()->route('purchasing.show', $purchase)
                ->with('error', "Purchase Order berstatus {$purchase->status} tidak dapat menerima barang fisik.");
        }

        $purchase->load(['items.product.unit', 'supplier', 'warehouse']);
        return view('purchasing.receive', compact('purchase'));
    }

    public function processReceive(ReceivePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validated();
        $receivingDate = Carbon::parse($validated['receiving_date']);

        try {
            $this->purchaseService->receiveItems(
                purchase: $purchase,
                receivedItems: $validated['items'],
                notes: $validated['notes'] ?? null,
                userId: auth()->id() ?? 1,
                receivingDate: $receivingDate
            );

            return redirect()->route('purchasing.show', $purchase)
                ->with('success', "Penerimaan barang untuk PO {$purchase->purchase_number} berhasil diproses dan stok telah ditambahkan ke gudang.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Membatalkan Purchase Order.
     */
    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->purchaseService->cancelPurchase($purchase, $request->input('reason'));
            return redirect()->route('purchasing.show', $purchase)
                ->with('success', "Purchase Order {$purchase->purchase_number} berhasil dibatalkan.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
