<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $ordersToday = Order::whereDate('order_date', $today)->count();
        $revenueToday = Order::whereDate('order_date', $today)->sum('grand_total');
        $avgOrderToday = $ordersToday > 0 ? $revenueToday / $ordersToday : 0;

        $ordersThisWeek = Order::whereDate('order_date', '>=', now()->startOfWeek())->count();
        $revenueThisWeek = Order::whereDate('order_date', '>=', now()->startOfWeek())->sum('grand_total');

        return [
            Stat::make('Orders Today', $ordersToday)
                ->description('Today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
            Stat::make('Revenue Today', '₹' . number_format($revenueToday, 0))
                ->description('Today')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('primary'),
            Stat::make('Avg Order Value', '₹' . number_format($avgOrderToday, 0))
                ->description('Today')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
            Stat::make('Orders This Week', $ordersThisWeek)
                ->description('Week to date')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('Revenue This Week', '₹' . number_format($revenueThisWeek, 0))
                ->description('Week to date')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('danger'),
        ];
    }
}
