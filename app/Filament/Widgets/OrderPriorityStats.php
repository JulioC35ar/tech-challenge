<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderPriorityStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('High Priority', MaintenanceOrder::where('priority', 'high')->count())
                ->description('Total high priority orders')
                ->color('danger'),

            Stat::make('Medium Priority', MaintenanceOrder::where('priority', 'medium')->count())
                ->description('Total medium priority orders')
                ->color('warning'),

            Stat::make('Low Priority', MaintenanceOrder::where('priority', 'low')->count())
                ->description('Total low priority orders')
                ->color('success'),
        ];
    }
}
