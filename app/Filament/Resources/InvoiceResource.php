<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $modelLabel = 'Invoice';
    protected static ?string $pluralModelLabel = 'Invoice';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Invoice')->schema([
                Forms\Components\Select::make('sales_order_id')
                    ->label('Sales Order')
                    ->options(
                        SalesOrder::whereIn('status', ['confirmed', 'delivered'])
                            ->get()
                            ->mapWithKeys(fn($so) => [$so->id => "{$so->number} — {$so->customer->name}"])
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $so = SalesOrder::with('customer')->find($state);
                            if ($so) {
                                $set('customer_id', $so->customer_id);
                                $set('subtotal', $so->subtotal);
                                $set('tax_amount', $so->tax_amount);
                                $set('total_amount', $so->total_amount);
                            }
                        }
                    }),
                Forms\Components\Select::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label('Nomor Invoice')
                    ->disabled()
                    ->placeholder('Otomatis'),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal Invoice')
                    ->required()
                    ->default(now()),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Jatuh Tempo')
                    ->required()
                    ->default(now()->addDays(30)),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Ringkasan Tagihan')->schema([
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('PPN')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Tagihan')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('paid_amount')
                    ->label('Sudah Dibayar')
                    ->prefix('Rp')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn(Invoice $record) =>
                        $record->status !== 'paid' && $record->due_date->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'draft'     => 'gray',
                        'sent'      => 'info',
                        'partial'   => 'warning',
                        'paid'      => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim',
                        'partial'   => 'Bayar Sebagian',
                        'paid'      => 'Lunas',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Terbayar')
                    ->money('IDR'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim',
                        'partial'   => 'Bayar Sebagian',
                        'paid'      => 'Lunas',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                // Kirim invoice (draft → sent)
                Tables\Actions\Action::make('send')
                    ->label('Kirim')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn(Invoice $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->update(['status' => 'sent']);
                        Notification::make()->title('Invoice terkirim!')->success()->send();
                    }),

                // Catat pembayarann
                Tables\Actions\Action::make('pay')
                    ->label('Catat Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn(Invoice $r) => in_array($r->status, ['sent', 'partial']))
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Pembayaran (Rp)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Bayar')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('method')
                            ->label('Metode Bayar')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'cash'     => 'Tunai',
                                'giro'     => 'Giro',
                            ])
                            ->default('transfer'),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $paid = (float) $record->paid_amount + (float) $data['amount'];
                        $total = (float) $record->total_amount;

                        $status = $paid >= $total ? 'paid' : 'partial';
                        $record->update([
                            'paid_amount' => min($paid, $total),
                            'status'      => $status,
                        ]);

                        // Update status SO jika sudah lunas
                        if ($status === 'paid' && $record->salesOrder) {
                            $record->salesOrder->update(['status' => 'invoiced']);
                        }

                        Notification::make()
                            ->title($status === 'paid' ? '✅ Invoice LUNAS!' : '💰 Pembayaran dicatat')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('print')
                    ->label('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Invoice $record): string => route('admin.invoices.print', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
