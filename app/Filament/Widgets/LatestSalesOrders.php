<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestSalesOrders extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Sales Order Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(SalesOrder::latest()->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('No. SO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'draft'     => 'gray',
                        'confirmed' => 'info',
                        'delivered' => 'success',
                        'invoiced'  => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'draft'     => 'Draft',
                        'confirmed' => 'Dikonfirmasi',
                        'delivered' => 'Terkirim',
                        'invoiced'  => 'Ditagih',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
            ]);
    }
}
