<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceOrder;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class OrdersByTechnicianChart extends ChartWidget
{
    protected static ?string $heading = 'Orders per Technician';
    protected static ?string $chartHeight = '300px';

    protected function getData(): array
    {
        $data = MaintenanceOrder::query()
            ->whereNotNull('technician_id')
            ->selectRaw('technician_id, COUNT(*) as total')
            ->groupBy('technician_id')
            ->pluck('total', 'technician_id');

        $labels = [];
        $values = [];

        foreach ($data as $technicianId => $count) {
            $technician = User::find($technicianId);
            $labels[] = $technician?->name ?? 'Unknown';
            $values[] = $count;
        }

        return [
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => [
                        '#93c5fd', '#facc15', '#4ade80', '#f87171', '#a78bfa',
                        '#fb923c', '#fcd34d', '#34d399', '#60a5fa', '#c084fc',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 8,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            'layout' => [
                'padding' => [
                    'top' => 10,
                    'bottom' => 10,
                ],
            ],
        ];
    }
}
