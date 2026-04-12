<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class AttendanceDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'HRIS';
    protected static ?string $title = 'Ringkasan Kehadiran';
    protected static ?string $navigationLabel = 'Ringkasan';
    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.attendance-dashboard';

    public $todayAttendance;
    public $currentTime;
    public $employee;

    // Debug info
    public $debugInfo = [];

    public function mount()
    {
        $user = Auth::user();
        $this->employee = Employee::where('user_id', $user->id)->first() ?: Employee::first();
        $this->refreshAttendance();
        $this->currentTime = now()->format('H:i:s');
    }

    public function selectEmployee($id)
    {
        $this->employee = Employee::find($id);
        $this->refreshAttendance();
    }

    public function refreshAttendance()
    {
        $this->todayAttendance = null;
        if ($this->employee) {
            $this->todayAttendance = Attendance::where('employee_id', $this->employee->id)
                ->whereNull('check_out')
                ->latest()
                ->first();
        }
    }

    public function punchIn()
    {
        if (!$this->employee) {
            Notification::make()->title('Pilih profil Terlebih dahulu.')->danger()->send();
            return;
        }

        Attendance::create([
            'employee_id' => $this->employee->id,
            'date' => now()->toDateString(),
            'check_in' => now()->toTimeString(),
            'status' => 'present',
        ]);

        $this->refreshAttendance();
        Notification::make()->title('Berhasil Masuk (Punch In)')->success()->send();
    }

    public function punchOut()
    {
        if ($this->employee) {
            // Close ALL active sessions to prevent backlog
            Attendance::where('employee_id', $this->employee->id)
                ->whereNull('check_out')
                ->update(['check_out' => now()->toTimeString()]);
        }

        $this->refreshAttendance();
        Notification::make()->title('Berhasil Keluar (Punch Out)')->success()->send();
    }
}
