<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Master Data Routes
    Route::prefix('master-data')->group(function () {
        // Categories
        Route::patch('categories/{category}/toggle-status', [\App\Http\Controllers\MasterData\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::resource('categories', \App\Http\Controllers\MasterData\CategoryController::class)->except(['show']);

        // Units
        Route::patch('units/{unit}/toggle-status', [\App\Http\Controllers\MasterData\UnitController::class, 'toggleStatus'])->name('units.toggle-status');
        Route::resource('units', \App\Http\Controllers\MasterData\UnitController::class)->except(['show']);

        // Suppliers
        Route::patch('suppliers/{supplier}/toggle-status', [\App\Http\Controllers\MasterData\SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
        Route::resource('suppliers', \App\Http\Controllers\MasterData\SupplierController::class);

        // Warehouses & Internal Locations
        Route::patch('warehouses/{warehouse}/toggle-status', [\App\Http\Controllers\MasterData\WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');
        Route::resource('warehouses', \App\Http\Controllers\MasterData\WarehouseController::class);

        Route::post('warehouses/{warehouse}/locations', [\App\Http\Controllers\MasterData\WarehouseLocationController::class, 'store'])->name('warehouses.locations.store');
        Route::put('warehouses/{warehouse}/locations/{location}', [\App\Http\Controllers\MasterData\WarehouseLocationController::class, 'update'])->name('warehouses.locations.update');
        Route::delete('warehouses/{warehouse}/locations/{location}', [\App\Http\Controllers\MasterData\WarehouseLocationController::class, 'destroy'])->name('warehouses.locations.destroy');
        Route::patch('warehouses/{warehouse}/locations/{location}/toggle-status', [\App\Http\Controllers\MasterData\WarehouseLocationController::class, 'toggleStatus'])->name('warehouses.locations.toggle-status');

        // Products
        Route::patch('products/{product}/toggle-status', [\App\Http\Controllers\MasterData\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::resource('products', \App\Http\Controllers\MasterData\ProductController::class);
    });

    // Inventory Core & Transaction Ledger
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Inventory\InventoryController::class, 'overview'])->name('overview');
        
        // Stock In
        Route::get('/stock-in', [\App\Http\Controllers\Inventory\InventoryController::class, 'stockInForm'])->name('stock-in');
        Route::post('/stock-in', [\App\Http\Controllers\Inventory\InventoryController::class, 'processStockIn'])->name('stock-in.process');

        // Stock Out
        Route::get('/stock-out', [\App\Http\Controllers\Inventory\InventoryController::class, 'stockOutForm'])->name('stock-out');
        Route::post('/stock-out', [\App\Http\Controllers\Inventory\InventoryController::class, 'processStockOut'])->name('stock-out.process');

        // Transfer
        Route::get('/transfer', [\App\Http\Controllers\Inventory\InventoryController::class, 'transferForm'])->name('transfer');
        Route::post('/transfer', [\App\Http\Controllers\Inventory\InventoryController::class, 'processTransfer'])->name('transfer.process');

        // Adjustment / Opname
        Route::get('/adjustment', [\App\Http\Controllers\Inventory\InventoryController::class, 'adjustmentForm'])->name('adjustment');
        Route::post('/adjustment', [\App\Http\Controllers\Inventory\InventoryController::class, 'processAdjustment'])->name('adjustment.process');

        // Dynamic AJAX Stock Checker
        Route::get('/api/stock', [\App\Http\Controllers\Inventory\InventoryController::class, 'getStockAjax'])->name('api.stock');

        // Stock Card (Kartu Stok)
        Route::get('/stock-card', [\App\Http\Controllers\Inventory\StockCardController::class, 'index'])->name('stock-card');
    });

    // Purchasing & Procurement
    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/edit', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'edit'])->name('edit');
        Route::put('/{purchase}', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'update'])->name('update');
        Route::delete('/{purchase}', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'destroy'])->name('destroy');
        Route::patch('/{purchase}/order', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'order'])->name('order');
        Route::get('/{purchase}/receive', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'receiveForm'])->name('receive');
        Route::post('/{purchase}/receive', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'processReceive'])->name('receive.process');
        Route::patch('/{purchase}/cancel', [\App\Http\Controllers\Purchasing\PurchaseController::class, 'cancel'])->name('cancel');
    });

    // Sales & Outgoing Orders
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Sales\SaleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Sales\SaleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Sales\SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [\App\Http\Controllers\Sales\SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/edit', [\App\Http\Controllers\Sales\SaleController::class, 'edit'])->name('edit');
        Route::put('/{sale}', [\App\Http\Controllers\Sales\SaleController::class, 'update'])->name('update');
        Route::patch('/{sale}/complete', [\App\Http\Controllers\Sales\SaleController::class, 'complete'])->name('complete');
        Route::patch('/{sale}/cancel', [\App\Http\Controllers\Sales\SaleController::class, 'cancel'])->name('cancel');
    });
});



