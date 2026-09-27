<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\SalesService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Display a listing of sales.
     */
    public function index(Request $request): View
    {
        $query = Sale::with(['warehouse', 'creator', 'items'])
            ->latest('sale_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        $sales = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('sales.index', compact('sales', 'warehouses'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['stocks'])
            ->orderBy('name')
            ->get();
        $invoiceNumber = $this->salesService->generateInvoiceNumber();

        return view('sales.create', compact('warehouses', 'products', 'invoiceNumber'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(SaleRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            $sale = $this->salesService->createSale($data);

            $msg = $sale->isCompleted()
                ? "Penjualan {$sale->invoice_number} berhasil dibuat dan stok gudang telah dikurangi."
                : "Draft Penjualan {$sale->invoice_number} berhasil disimpan.";

            return redirect()->route('sales.show', $sale)
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale): View
    {
        $sale->load(['warehouse', 'creator', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale): View|RedirectResponse
    {
        if (!$sale->isDraft()) {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Hanya penjualan berstatus DRAFT yang dapat diedit.');
        }

        $sale->load(['items.product']);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['stocks'])
            ->orderBy('name')
            ->get();

        return view('sales.edit', compact('sale', 'warehouses', 'products'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(SaleRequest $request, Sale $sale): RedirectResponse
    {
        try {
            $data = $request->validated();
            $sale = $this->salesService->updateSale($sale, $data);

            $msg = $sale->isCompleted()
                ? "Penjualan {$sale->invoice_number} berhasil diselesaikan dan stok gudang telah dikurangi."
                : "Penjualan {$sale->invoice_number} berhasil diperbarui.";

            return redirect()->route('sales.show', $sale)
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Complete a draft sale and deduct stock.
     */
    public function complete(Sale $sale): RedirectResponse
    {
        try {
            $this->salesService->completeSale($sale);

            return redirect()->route('sales.show', $sale)
                ->with('success', "Penjualan {$sale->invoice_number} berhasil diselesaikan dan stok telah dipotong dari gudang.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a sale (restoring stock if previously completed).
     */
    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:255',
        ]);

        try {
            $this->salesService->cancelSale($sale, $request->cancellation_reason);

            return redirect()->route('sales.show', $sale)
                ->with('success', "Penjualan {$sale->invoice_number} berhasil dibatalkan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan penjualan: ' . $e->getMessage());
        }
    }
}
