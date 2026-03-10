<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Models\Product;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $modelLabel = 'Sales Order';
    protected static ?string $pluralModelLabel = 'Sales Order';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pesanan')->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label('Nomor SO')
                    ->disabled()
                    ->placeholder('Otomatis'),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Berlaku Sampai'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Item Pesanan')->schema([
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
                                        $set('unit_price', $product->sales_price);
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
                                $set('subtotal', self::calculateSubtotal($get)))
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
                                $set('subtotal', self::calculateSubtotal($get)))
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('discount_percent')
                            ->label('Diskon %')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) =>
                                $set('subtotal', self::calculateSubtotal($get)))
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('tax_percent')
                            ->label('PPN %')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) =>
                                $set('subtotal', self::calculateSubtotal($get)))
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->columnSpan(2),
                    ])
                    ->columns(11)
                    ->addActionLabel('Tambah Item')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updateTotals($get, $set);
                    })
                    ->deleteAction(fn($action) => $action->after(function (Get $get, Set $set) {
                        self::updateTotals($get, $set);
                    })),
            ]),

            Forms\Components\Section::make('Ringkasan')->schema([
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Total Diskon')
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
            ])->columns(4),
        ]);
    }

    protected static function calculateSubtotal(Get $get): float
    {
        $qty = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('unit_price') ?? 0);
        $discount = (float) ($get('discount_percent') ?? 0);
        $tax = (float) ($get('tax_percent') ?? 0);

        $base = $qty * $price;
        $discounted = $base - ($base * $discount / 100);
        $total = $discounted + ($discounted * $tax / 100);

        return round($total, 2);
    }

    protected static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        $discount = 0;
        $tax = 0;

        foreach ($items as $item) {
            $qty   = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $disc  = (float) ($item['discount_percent'] ?? 0);
            $ppn   = (float) ($item['tax_percent'] ?? 0);

            $base     = $qty * $price;
            $discAmt  = $base * $disc / 100;
            $taxAmt   = ($base - $discAmt) * $ppn / 100;

            $subtotal += $base;
            $discount += $discAmt;
            $tax      += $taxAmt;
        }

        $set('subtotal', round($subtotal, 2));
        $set('discount_amount', round($discount, 2));
        $set('tax_amount', round($tax, 2));
        $set('total_amount', round($subtotal - $discount + $tax, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('No. SO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
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
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'confirmed' => 'Dikonfirmasi',
                        'delivered' => 'Terkirim',
                        'invoiced'  => 'Ditagih',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(SalesOrder $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => 'confirmed']);
                        Notification::make()->title('SO dikonfirmasi!')->success()->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(SalesOrder $record) => in_array($record->status, ['draft', 'confirmed']))
                    ->requiresConfirmation()
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()->title('SO dibatalkan.')->warning()->send();
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
            'index'  => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit'   => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
