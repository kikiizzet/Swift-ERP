<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 h-full min-h-[750px]">
        
        <!-- Left: Products Area -->
        <div class="md:col-span-8 flex flex-col gap-6">
            
            <!-- Header: Search & Categories -->
            <div class="fi-section p-6 rounded-xl bg-white dark:bg-gray-800 border dark:border-gray-700 shadow-sm">
                <div class="flex flex-col gap-4">
                    <div class="relative">
                        <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                            <x-filament::input
                                type="text"
                                placeholder="Cari nama produk atau SKU..."
                                wire:model.live.debounce.300ms="search"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
                        <button 
                            wire:click="setCategory(null)"
                            @class([
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                                'bg-primary-600 text-white shadow-md' => $activeCategoryId === null,
                                'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200' => $activeCategoryId !== null,
                            ])
                        >
                            Semua Produk
                        </button>
                        @foreach($this->categories as $category)
                            <button 
                                wire:click="setCategory({{ $category->id }})"
                                @class([
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                                    'bg-primary-600 text-white shadow-md' => $activeCategoryId === $category->id,
                                    'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200' => $activeCategoryId !== $category->id,
                                ])
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto pr-2 scrollbar-thin" style="max-height: 600px;">
                @forelse($this->products as $product)
                    <div 
                        wire:click="addToCart({{ $product->id }})"
                        class="fi-section group relative overflow-hidden bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl cursor-pointer hover:border-primary-500 hover:shadow-lg transition-all"
                    >
                        <!-- Badge Stok -->
                        <div @class([
                            'absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase z-10',
                            'bg-green-100 text-green-700' => $product->stock_quantity > 0,
                            'bg-red-100 text-red-700' => $product->stock_quantity <= 0,
                        ])>
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </div>

                        <!-- Image Area -->
                        <div class="aspect-square bg-gray-50 dark:bg-gray-900 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                                <img src="/storage/{{ $product->image }}" class="w-full h-full object-cover transition-transform group-hover:scale-110" alt="{{ $product->name }}">
                            @else
                                <div class="flex flex-col items-center text-gray-400">
                                    <x-filament::icon icon="heroicon-o-photo" class="w-12 h-12 mb-1" />
                                    <span class="text-[10px] uppercase font-bold tracking-wider">No Image</span>
                                </div>
                            @endif
                        </div>

                        <!-- Info Area -->
                        <div class="p-3">
                            <h3 class="font-bold text-gray-900 dark:text-white truncate text-sm mb-1">{{ $product->name }}</h3>
                            <p class="text-xs text-gray-500 mb-2 truncate">SKU: {{ $product->sku }}</p>
                            <div class="text-primary-600 dark:text-primary-400 font-bold">
                                Rp {{ number_format($product->sales_price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-500">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-16 h-16 mx-auto mb-4 opacity-20" />
                        <p>Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Cart & Checkout Area -->
        <div class="md:col-span-4 flex flex-col gap-6">
            <div class="fi-section bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl flex flex-col h-full shadow-sm">
                
                <!-- Cart Header -->
                <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                    <h2 class="font-bold text-lg flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-shopping-cart" class="w-5 h-5 text-primary-500" />
                        Keranjang
                    </h2>
                    <span class="px-2 py-1 bg-primary-100 text-primary-700 rounded text-xs font-bold">{{ count($cart) }} Items</span>
                </div>

                <!-- Cart Items -->
                <div class="flex-grow overflow-y-auto p-4 flex flex-col gap-4 scrollbar-thin" style="max-height: 400px;">
                    @forelse($cart as $id => $item)
                        <div class="flex gap-3 group">
                            <div class="w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex-shrink-0 overflow-hidden">
                                @if($item['image'])
                                    <img src="/storage/{{ $item['image'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-filament::icon icon="heroicon-o-photo" class="w-6 h-6 text-gray-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="text-sm font-bold truncate leading-tight dark:text-white">{{ $item['name'] }}</h4>
                                    <button wire:click="removeFromCart({{ $id }})" class="text-gray-400 hover:text-red-500 transition-colors">
                                        <x-filament::icon icon="heroicon-m-x-mark" class="w-4 h-4" />
                                    </button>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2 border dark:border-gray-600 rounded-lg p-0.5">
                                        <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] - 1 }})" class="w-6 h-6 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                            <x-filament::icon icon="heroicon-m-minus" class="w-3 h-3" />
                                        </button>
                                        <span class="text-xs font-bold w-6 text-center">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] + 1 }})" class="w-6 h-6 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                            <x-filament::icon icon="heroicon-m-plus" class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <span class="font-bold text-sm">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-400">
                            <p>Keranjang masih kosong</p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer: Payment & Total -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-700 rounded-b-xl flex flex-col gap-4">
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer'] as $val => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="paymentMethod" value="{{ $val }}" class="sr-only peer">
                                    <div @class([
                                        'px-2 py-3 text-center rounded-lg border text-[10px] font-bold transition-all peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700 dark:border-gray-700 dark:peer-checked:bg-primary-900/30',
                                        'bg-white dark:bg-gray-800' => $paymentMethod !== $val
                                    ])>
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Show QRIS Mock -->
                    @if($paymentMethod === 'qris')
                        <div class="p-4 bg-white rounded-lg border border-dashed text-center flex flex-col items-center gap-2">
                            <div class="w-32 h-32 bg-gray-100 flex items-center justify-center border rounded">
                                <x-filament::icon icon="heroicon-o-qr-code" class="w-24 h-24 text-gray-800" />
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold uppercase">Scan untuk membayar</span>
                        </div>
                    @endif

                    <div class="space-y-2 pt-2 border-t dark:border-gray-700">
                        <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center font-black text-xl text-gray-900 dark:text-white">
                            <span>Total</span>
                            <span class="text-primary-600">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <x-filament::button 
                        size="xl" 
                        wire:click="checkout"
                        :disabled="empty($cart)"
                        class="w-full shadow-lg shadow-primary-500/20"
                    >
                        Checkout & Bayar
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .scrollbar-thin::-webkit-scrollbar { width: 4px; height: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</x-filament-panels::page>
