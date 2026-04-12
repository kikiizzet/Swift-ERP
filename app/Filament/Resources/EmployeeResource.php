<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'HRIS';
    protected static ?string $modelLabel = 'Karyawan';
    protected static ?string $pluralModelLabel = 'Karyawan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Pribadi')->schema([
                Forms\Components\TextInput::make('employee_number')
                    ->label('ID Karyawan')
                    ->placeholder('Otomatis jika kosong')
                    ->maxLength(20),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->label('No. Telepon')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\TextInput::make('id_number')
                    ->label('NIK (KTP)')
                    ->maxLength(20),
                Forms\Components\Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options(['male' => 'Laki-laki', 'female' => 'Perempuan']),
                Forms\Components\DatePicker::make('birth_date')
                    ->label('Tanggal Lahir'),
                Forms\Components\Select::make('marital_status')
                    ->label('Status Pernikahan')
                    ->options(['single' => 'Belum Menikah', 'married' => 'Menikah', 'divorced' => 'Cerai']),
                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Data Pekerjaan')->schema([
                Forms\Components\Select::make('department_id')
                    ->label('Departemen')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('job_position_id')
                    ->label('Jabatan')
                    ->relationship(
                        'jobPosition',
                        'name',
                        fn (Forms\Get $get, \Illuminate\Database\Eloquent\Builder $query) => 
                            $query->when($get('department_id'), fn($q) => $q->where('department_id', $get('department_id')))
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Jabatan')
                            ->required(),
                        Forms\Components\Select::make('department_id')
                            ->label('Departemen')
                            ->relationship('department', 'name')
                            ->default(fn(Forms\Get $get) => $get('../../department_id')),
                    ]),
                Forms\Components\Select::make('status')
                    ->label('Status Kepegawaian')
                    ->options([
                        'active'    => 'Aktif',
                        'inactive'  => 'Tidak Aktif',
                        'resigned'  => 'Resign',
                    ])
                    ->default('active'),
                Forms\Components\DatePicker::make('join_date')
                    ->label('Tanggal Masuk')
                    ->default(now()),
            ])->columns(2),

            Forms\Components\Section::make('Informasi Bank (Opsional)')->schema([
                Forms\Components\TextInput::make('tax_number')
                    ->label('NPWP')
                    ->maxLength(30),
                Forms\Components\TextInput::make('bank_name')
                    ->label('Nama Bank'),
                Forms\Components\TextInput::make('bank_account_number')
                    ->label('No. Rekening'),
            ])->columns(3)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_number')
                    ->label('ID')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('jobPosition.name')
                    ->label('Jabatan'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'resigned' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'active'   => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'resigned' => 'Resign',
                        default    => $state,
                    }),
                Tables\Columns\TextColumn::make('join_date')
                    ->label('Tgl Masuk')
                    ->date('d M Y'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Departemen')
                    ->relationship('department', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'resigned' => 'Resign']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit'   => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
