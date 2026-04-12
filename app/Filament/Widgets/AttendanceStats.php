<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceStats extends BaseWidget
{
    protected function getStats(): array
    {
        $employee = Auth::user()->employee;
        if (!$employee) return [];

        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        // Today's Stats
        $todayAttendances = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->get();
        
        $todaySeconds = 0;
        foreach ($todayAttendances as $att) {
            if ($att->check_in && $att->check_out) {
                $todaySeconds += Carbon::parse($att->check_in)->diffInSeconds(Carbon::parse($att->check_out));
            } elseif ($att->check_in) {
                $todaySeconds += Carbon::parse($att->check_in)->diffInSeconds(now());
            }
        }

        // This Week's Stats
        $weekAttendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();
        
        $weekSeconds = 0;
        foreach ($weekAttendances as $att) {
            if ($att->check_in && $att->check_out) {
                $weekSeconds += Carbon::parse($att->check_in)->diffInSeconds(Carbon::parse($att->check_out));
            } elseif ($att->check_in && $att->date->isToday()) {
                 $weekSeconds += Carbon::parse($att->check_in)->diffInSeconds(now());
            }
        }

        return [
            Stat::make('Total Hours (Today)', $this->formatSeconds($todaySeconds))
                ->description('Work time today')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->icon('heroicon-m-clock'),
            Stat::make('Total Hours (This Week)', $this->formatSeconds($weekSeconds))
                ->description('Work time this week')
                ->color('info')
                ->icon('heroicon-m-calendar-days'),
             Stat::make('Status', $todayAttendances->last()?->check_out ? 'Finished' : ($todayAttendances->last()?->check_in ? 'Working' : 'Not Started'))
                ->description('Current attendance status')
                ->color(fn() => $todayAttendances->last()?->check_out ? 'gray' : ($todayAttendances->last()?->check_in ? 'success' : 'warning'))
                ->icon('heroicon-m-signal'),
        ];
    }

    protected function formatSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds % 60);
    }
}
