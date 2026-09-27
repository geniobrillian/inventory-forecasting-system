<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    /**
     * Display the main analytics dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $kpi = $this->analyticsService->getKpiMetrics();
        $health = $this->analyticsService->getStockHealthCounts();
        $velocity = $this->analyticsService->getVelocityMetrics();
        $demandChart = $this->analyticsService->getDemandTrendChartData(14);
        $movementChart = $this->analyticsService->getStockMovementChartData(6);
        $warehouseChart = $this->analyticsService->getWarehouseDistributionChartData();
        $recentTransactions = $this->analyticsService->getRecentTransactions(8);
        $topSelling = $this->analyticsService->getTopSellingProducts(5);

        return view('dashboard', [
            'user' => $user,
            'kpi' => $kpi,
            'health' => $health,
            'velocity' => $velocity,
            'demandChart' => $demandChart,
            'movementChart' => $movementChart,
            'warehouseChart' => $warehouseChart,
            'recentTransactions' => $recentTransactions,
            'topSelling' => $topSelling,
        ]);
    }
}
