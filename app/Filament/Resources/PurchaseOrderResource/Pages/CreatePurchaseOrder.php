<?php namespace App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();
        $data['number']     = \App\Models\PurchaseOrder::generateNumber();
        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        $this->recalculateTotals($this->record);
    }

    public static function recalculateTotals(\App\Models\PurchaseOrder $po): void
    {
        $po->refresh();
        $subtotal = $po->items->sum(fn($i) => (float) $i->quantity * (float) $i->unit_price);
        $tax      = $po->items->sum(fn($i) => (float) $i->quantity * (float) $i->unit_price * ((float) ($i->tax_percent ?? 0) / 100));
        $po->update([
            'subtotal'     => round($subtotal, 2),
            'tax_amount'   => round($tax, 2),
            'total_amount' => round($subtotal + $tax, 2),
        ]);
    }
}
