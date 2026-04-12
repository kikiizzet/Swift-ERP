<x-filament-panels::page>
    <div class="fi-section" style="max-width: 100%;">
        <x-filament::grid default="1" md="12" class="gap-8 items-start">
            
            <!-- Sidebar Profile (4/12) -->
            <x-filament::grid.column default="4">
                <div class="bg-gray-900 rounded-[2.5rem] p-10 border border-gray-800 shadow-2xl flex flex-col items-center">
                    
                    <!-- Large Circle Avatar -->
                    <div class="w-32 h-32 rounded-full bg-white flex items-center justify-center mb-10 shadow-2xl aspect-square flex-shrink-0 border-8 border-gray-800">
                        <span class="text-4xl font-black text-gray-900 italic tracking-tighter">
                            {{ substr($this->employee?->name ?? 'U', 0, 1) }}{{ substr(strrchr($this->employee?->name ?? ' ', ' '), 1, 1) }}
                        </span>
                    </div>

                    <div class="text-center space-y-4 mb-10">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-[0.5em]">Selamat Datang,</p>
                        <h2 class="text-3xl font-black text-white tracking-tighter leading-tight">
                            {{ $this->employee?->name ?? 'User' }}
                        </h2>
                        <p class="text-xs font-bold text-gray-500 tracking-widest">{{ $this->employee?->jobPosition?->name ?? 'Junior Backend Dev' }}</p>
                    </div>

                    <!-- Digital Clock (Large) -->
                    <div class="text-center mb-10 w-full" x-data="{ time: '' }" x-init="setInterval(() => { time = new Date().toLocaleTimeString('en-GB') }, 1000)">
                        <h3 class="text-4xl font-black text-white tracking-tight tabular-nums" x-text="time">00:00:00</h3>
                        <div class="mt-3 inline-flex items-center gap-3 px-4 py-2 bg-black/50 rounded-full border border-gray-800">
                            <span class="text-[10px] font-black text-primary-500 uppercase">{{ now()->format('l') }}</span>
                            <span class="text-gray-700">|</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ now()->format('d M Y') }}</span>
                        </div>
                    </div>

                    <!-- Action Button (Floating Pill) -->
                    @php $isPunchedIn = $this->todayAttendance && !$this->todayAttendance->check_out; @endphp
                    <div class="w-full">
                        @if(!$isPunchedIn)
                            <button wire:click="punchIn" 
                                    style="background-color: #22c55e;"
                                    class="w-full text-white font-black py-5 rounded-full flex items-center justify-center gap-4 transition-all hover:scale-[1.02] active:scale-[0.98] shadow-[0_15px_30px_rgba(34,197,94,0.25)] uppercase tracking-widest text-xs">
                                <x-filament::icon icon="heroicon-m-finger-print" class="w-6 h-6" />
                                PUNCH IN
                            </button>
                        @else
                            <button wire:click="punchOut" 
                                    style="background-color: #ef4444;"
                                    class="w-full text-white font-black py-5 rounded-full flex items-center justify-center gap-4 transition-all hover:scale-[1.02] active:scale-[0.98] shadow-[0_15px_30px_rgba(239,68,68,0.25)] uppercase tracking-widest text-xs">
                                <x-filament::icon icon="heroicon-m-power" class="w-6 h-6" />
                                PUNCH OUT
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Profile Switcher -->
                @php $allEmployees = \App\Models\Employee::all(); @endphp
                @if($allEmployees->count() > 1)
                    <div class="mt-6">
                         <select wire:change="selectEmployee($event.target.value)" 
                                class="w-full bg-gray-900 border-gray-800 rounded-3xl text-[10px] font-black text-gray-400 py-3 focus:ring-primary-500 cursor-pointer px-6">
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ $this->employee?->id == $emp->id ? 'selected' : '' }}>
                                    Aktifkan Profil: {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </x-filament::grid.column>

            <!-- Main Content Area (8/12) -->
            <x-filament::grid.column default="8" class="space-y-8">
                <!-- Weekly Performance -->
                <div class="bg-gray-900 rounded-[2.5rem] p-8 border border-gray-800 shadow-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-8 px-2">
                        <div>
                            <h4 class="text-xl font-black text-white tracking-tighter">Weekly Work Hours</h4>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1">Productivity Analysis</p>
                        </div>
                    </div>
                    <div class="h-[350px] -mx-4 -mb-4">
                        @livewire(\App\Filament\Widgets\AttendanceChart::class)
                    </div>
                </div>

                <!-- Activity Ledger -->
                <div class="bg-gray-900 rounded-[2.5rem] p-8 border border-gray-800 shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between mb-8 px-2">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-primary-500 rounded-full"></div>
                            <h4 class="text-xl font-black text-white tracking-tighter text uppercase">Aktivitas Terkini</h4>
                        </div>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Last 4 Entries</span>
                    </div>

                    <div class="divide-y divide-gray-800" wire:poll.10s>
                        @php
                            $activities = \App\Models\Attendance::where('employee_id', $this->employee?->id)
                                ->orderBy('date', 'desc')
                                ->orderBy('check_in', 'desc')
                                ->limit(4)
                                ->get();
                        @endphp
                        @forelse($activities as $activity)
                            <div class="py-5 flex items-center justify-between group px-2">
                                <div class="flex items-center gap-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-white uppercase tracking-tighter">{{ $activity->date->format('d M Y') }}</span>
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ $activity->date->format('l') }}</span>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-8">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-mono font-black text-white">
                                            {{ \Carbon\Carbon::parse($activity->check_in)->format('H:i') }} 
                                            @if($activity->check_out)
                                                <span class="text-gray-700">→</span> {{ \Carbon\Carbon::parse($activity->check_out)->format('H:i') }}
                                            @endif
                                        </span>
                                        @if($activity->check_out)
                                            @php $diff = \Carbon\Carbon::parse($activity->check_in)->diff(\Carbon\Carbon::parse($activity->check_out)); @endphp
                                            <span class="text-[10px] font-black text-primary-500 uppercase">{{ ($diff->h > 0 ? $diff->h.'H ' : '') . $diff->i.'M' }} TOTAL</span>
                                        @else
                                            <span class="text-success-500 text-[10px] font-black uppercase tracking-widest animate-pulse">Running Session</span>
                                        @endif
                                    </div>
                                    <div @class([
                                        'w-2 h-2 rounded-full',
                                        'bg-success-500' => $activity->check_out,
                                        'bg-primary-500 animate-ping' => !$activity->check_out,
                                    ])></div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-gray-600 font-bold uppercase tracking-widest">No Data Recorded</div>
                        @endforelse
                    </div>
                </div>
            </x-filament::grid.column>
        </x-filament::grid>
    </div>
</x-filament-panels::page>
