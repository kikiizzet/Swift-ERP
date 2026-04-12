<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Biasanya pergerakan stok dicatat otomatis dari PO/SO, 
            // tapi kita bisa tambahkan Manual Adjustment jika perlu.
            // Actions\CreateAction::make(),
        ];
    }
}
