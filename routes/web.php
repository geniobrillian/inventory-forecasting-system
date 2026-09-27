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
});

