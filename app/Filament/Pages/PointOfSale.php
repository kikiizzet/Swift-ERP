<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PointOfSale extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $title = 'Point of Sale';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.point-of-sale';

    // State
    public $search = '';
    public $activeCategoryId = null;
    public $cart = [];
    public $paymentMethod = 'cash';
    public $customerId = null;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        // Set default customer (Guest) if exists, or create one
        $guest = Customer::firstOrCreate(['name' => 'Guest/Umum'], ['email' => 'guest@erp.local']);
        $this->customerId = $guest->id;
    }

    public function getProductsProperty(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->activeCategoryId, fn($q) => $q->where('category_id', $this->activeCategoryId))
            ->with('category')
            ->limit(24)
            ->get();
    }

    public function getCategoriesProperty(): Collection
    {
        return ProductCategory::all();
    }

    public function setCategory($id)
    {
        $this->activeCategoryId = $id;
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) return;
        
        if ($product->stock_quantity <= 0 && $product->type === 'storable') {
            Notification::make()->title('Stok habis!')->danger()->send();
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float)$product->sales_price,
                'quantity' => 1,
                'image' => $product->image,
                'unit' => $product->unit,
            ];
        }

        Notification::make()->title($product->name . ' ditambahkan')->success()->send();
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }
        
        $product = Product::find($productId);
        if ($product && $product->type === 'storable' && $quantity > $product->stock_quantity) {
             Notification::make()->title('Stok tidak mencukupi')->warning()->send();
             $this->cart[$productId]['quantity'] = $product->stock_quantity;
             return;
        }

        $this->cart[$productId]['quantity'] = $quantity;
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong!')->warning()->send();
            return;
        }

        DB::transaction(function () {
            $total = $this->total;
            
            // 1. Create Sales Order
            $so = SalesOrder::create([
                'customer_id' => $this->customerId,
                'status' => 'confirmed',
                'date' => now(),
                'subtotal' => $total,
                'total_amount' => $total,
                'created_by' => auth()->id(),
            ]);

            // 2. Create items & update stock
            foreach ($this->cart as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // 3. Create Invoice (Paid)
            $invoice = Invoice::create([
                'sales_order_id' => $so->id,
                'customer_id' => $this->customerId,
                'status' => 'paid',
                'date' => now(),
                'due_date' => now(),
                'subtotal' => $total,
                'total_amount' => $total,
                'paid_amount' => $total,
                'created_by' => auth()->id(),
            ]);

            $so->update(['status' => 'invoiced']);

            Notification::make()
                ->title('Checkout Berhasil!')
                ->body('Invoice #' . $invoice->number . ' telah dibuat.')
                ->success()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('print')
                        ->label('Cetak Struk')
                        ->url(route('admin.invoices.print', $invoice))
                        ->openUrlInNewTab()
                        ->color('success'),
                ])
                ->send();

            $this->cart = [];
            $this->search = '';
        });
    }
}
