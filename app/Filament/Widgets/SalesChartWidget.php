<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 0;
    protected static ?string $heading = 'Grafik Penjualan 6 Bulan Terakhir';
    protected static ?string $maxHeight = '250px';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i));

        $labels = $months->map(fn($m) => $m->translatedFormat('M Y'))->toArray();

        $data = $months->map(fn($m) =>
            SalesOrder::whereIn('status', ['confirmed', 'delivered', 'invoiced'])
                ->whereYear('date', $m->year)
                ->whereMonth('date', $m->month)
                ->sum('total_amount')
        )->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Total Penjualan (Rp)',
                    'data'            => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor'     => 'rgba(59, 130, 246, 1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
