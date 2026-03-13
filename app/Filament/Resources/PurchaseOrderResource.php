<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Pembelian';
    protected static ?string $modelLabel = 'Purchase Order';
    protected static ?string $pluralModelLabel = 'Purchase Order';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pembelian')->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Vendor/Supplier')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label('Nomor PO')
                    ->disabled()
                    ->placeholder('Otomatis'),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal PO')
                    ->required()
                    ->default(now()),
                Forms\Components\DatePicker::make('expected_delivery')
                    ->label('Estimasi Tiba'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Item Pembelian')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Produk')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('product_name', $product->name);
                                        $set('unit_price', $product->cost_price ?? 0);
                                        $set('unit', $product->unit);
                                    }
                                }
                            })
                            ->columnSpan(3),
                        Forms\Components\Hidden::make('product_name'),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Qty')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) =>
                                $set('subtotal', self::calculateItemSubtotal($get)))
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) =>
                                $set('subtotal', self::calculateItemSubtotal($get)))
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('tax_percent')
                            ->label('PPN %')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) =>
                                $set('subtotal', self::calculateItemSubtotal($get)))
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('received_quantity')
                            ->label('Diterima')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ])
                    ->columns(11)
                    ->addActionLabel('Tambah Item')
                    ->live()
                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updatePoTotals($get, $set))
                    ->deleteAction(fn($action) => $action->after(fn(Get $get, Set $set) =>
                        self::updatePoTotals($get, $set))),
            ]),

            Forms\Components\Section::make('Ringkasan')->schema([
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('Total PPN')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Bayar')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
            ])->columns(3),
        ]);
    }

    protected static function calculateItemSubtotal(Get $get): float
    {
        $qty   = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('unit_price') ?? 0);
        $tax   = (float) ($get('tax_percent') ?? 0);
        $base  = $qty * $price;
        return round($base + ($base * $tax / 100), 2);
    }

    protected static function updatePoTotals(Get $get, Set $set): void
    {
        $items    = $get('items') ?? [];
        $subtotal = 0;
        $tax      = 0;

        foreach ($items as $item) {
            $qty   = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $ppn   = (float) ($item['tax_percent'] ?? 0);
            $base  = $qty * $price;
            $subtotal += $base;
            $tax      += $base * $ppn / 100;
        }

        $set('subtotal', round($subtotal, 2));
        $set('tax_amount', round($tax, 2));
        $set('total_amount', round($subtotal + $tax, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
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
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim ke Vendor',
                        'received'  => 'Barang Diterima',
                        'billed'    => 'Sudah Ditagih',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Kirim ke Vendor')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn(PurchaseOrder $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => 'sent']);
                        Notification::make()->title('PO dikirim ke vendor!')->success()->send();
                    }),
                Tables\Actions\Action::make('receive')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn(PurchaseOrder $record) => $record->status === 'sent')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => 'received']);
                        // Update stok produk
                        foreach ($record->items as $item) {
                            \App\Models\StockMovement::record(
                                $item->product,
                                'in',
                                (float) $item->quantity,
                                "Penerimaan dari PO #{$record->number}",
                                PurchaseOrder::class,
                                $record->id,
                            );
                        }
                        Notification::make()->title('Barang diterima & stok diperbarui!')->success()->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(PurchaseOrder $record) => in_array($record->status, ['draft', 'sent']))
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()->title('PO dibatalkan.')->warning()->send();
                    }),
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
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
//feat: tambah fitur kecil
//feat: tambah fitur kecil
//feat: tambah fitur kecil
