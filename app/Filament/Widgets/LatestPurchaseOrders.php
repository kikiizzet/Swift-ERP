<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPurchaseOrders extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Purchase Order Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseOrder::latest()->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('No. PO'),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('expected_delivery')
                    ->label('Estimasi Tiba')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'draft'     => 'gray',
                        'sent'      => 'info',
                        'received'  => 'success',
                        'billed'    => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim ke Vendor',
                        'received'  => 'Barang Diterima',
                        'billed'    => 'Sudah Ditagih',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
            ]);
    }
}
