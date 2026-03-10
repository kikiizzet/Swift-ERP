<?php namespace App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();
        $data['number']     = \App\Models\SalesOrder::generateNumber();
        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        $this->recalculateTotals($this->record);
    }

    public static function recalculateTotals(\App\Models\SalesOrder $so): void
    {
        $so->refresh();
        $subtotal = 0; $discount = 0; $tax = 0;
        foreach ($so->items as $i) {
            $base    = (float) $i->quantity * (float) $i->unit_price;
            $disc    = $base * ((float) ($i->discount_percent ?? 0) / 100);
            $after   = $base - $disc;
            $taxAmt  = $after * ((float) ($i->tax_percent ?? 0) / 100);
            $subtotal += $base;
            $discount += $disc;
            $tax      += $taxAmt;
        }
        $so->update([
            'subtotal'         => round($subtotal, 2),
            'discount_amount'  => round($discount, 2),
            'tax_amount'       => round($tax, 2),
            'total_amount'     => round($subtotal - $discount + $tax, 2),
        ]);
    }
}
