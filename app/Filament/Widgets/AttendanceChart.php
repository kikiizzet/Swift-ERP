<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Weekly Work Hours';
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first() ?: Employee::where('name', 'like', '%Syauqi%')->first() ?: Employee::first();
        if (!$employee) return ['datasets' => [], 'labels' => []];

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get()
            ->groupBy(fn($att) => $att->date->format('Y-m-d'));

        $days = [];
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->toDateString();
            $days[] = $date->format('D');
            
            $dayAttendances = $attendances->get($dateString, collect());
            
            $seconds = 0;
            foreach ($dayAttendances as $att) {
                if ($att->check_in && $att->check_out) {
                    $seconds += abs(Carbon::parse($att->check_in)->diffInSeconds(Carbon::parse($att->check_out)));
                }
            }
            $data[] = round($seconds / 3600, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jam Kerja',
                    'data' => $data,
                    'backgroundColor' => '#6366f1',
                    'borderColor' => '#6366f1',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
