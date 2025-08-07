<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceOrder;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders by Status';
    protected static ?string $chartHeight = '300px';

    protected function getData(): array
    {
        $data = MaintenanceOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $colorMap = [
            'created' => '#93c5fd',
            'in_progress' => '#facc15',
            'pending_approval' => '#f97316',
            'approved' => '#4ade80',
            'rejected' => '#f87171',
        ];

        $labels = array_keys($data);

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_values($data),
                    'backgroundColor' => array_map(fn ($label) => $colorMap[$label] ?? '#a1a1aa', $labels),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
