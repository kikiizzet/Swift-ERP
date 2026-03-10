<?php namespace App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\SalesOrder;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();
        $data['number']     = \App\Models\Invoice::generateNumber();

        // Ambil total dari SO jika belum diisi
        if (empty($data['total_amount']) && ! empty($data['sales_order_id'])) {
            $so = SalesOrder::find($data['sales_order_id']);
            if ($so) {
                $data['subtotal']     = $so->subtotal;
                $data['tax_amount']   = $so->tax_amount;
                $data['total_amount'] = $so->total_amount;
            }
        }

        $record = parent::handleRecordCreation($data);

        // Tandai SO sudah ditagih
        if (! empty($data['sales_order_id'])) {
            SalesOrder::where('id', $data['sales_order_id'])->update(['status' => 'invoiced']);
        }

        return $record;
    }
}
