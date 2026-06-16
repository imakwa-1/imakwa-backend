<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Order::whereDate('created_at', today());
        $thisWeek = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $thisMonth = Order::whereMonth('created_at', now()->month);
        
        $todayRevenue = (clone $today)->paid()->sum('total');
        $thisMonthRevenue = (clone $thisMonth)->paid()->sum('total');
        $averageOrderValue = (clone $thisMonth)->paid()->avg('total') ?? 0;
        
        $pendingCount = Order::pending()->count();
        $processingCount = Order::processing()->count();
        $shippedCount = Order::shipped()->count();

        return [
            Stat::make('Today\'s Orders', $today->count())
                ->description('Revenue: $' . number_format($todayRevenue, 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('This Month', $thisMonth->count())
                ->description('Revenue: $' . number_format($thisMonthRevenue, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            
            Stat::make('Average Order Value', '$' . number_format($averageOrderValue, 2))
                ->description('This month')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            
            Stat::make('Pending', $pendingCount)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('In Progress', $processingCount + $shippedCount)
                ->description("{$processingCount} processing, {$shippedCount} shipped")
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }
}
