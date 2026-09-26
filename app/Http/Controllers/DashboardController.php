<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_suppliers' => Supplier::count(),
            'total_warehouses' => Warehouse::count(),
        ];

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
