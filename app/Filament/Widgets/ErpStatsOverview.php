<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ErpStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total penjualan bulan ini
        $salesThisMonth = SalesOrder::whereIn('status', ['confirmed', 'delivered', 'invoiced'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total_amount');

        // Total pembelian bulan ini
        $purchaseThisMonth = PurchaseOrder::whereIn('status', ['sent', 'received', 'billed'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total_amount');

        // Jumlah SO draft yang belum dikonfirmasi
        $pendingSO = SalesOrder::where('status', 'draft')->count();

        // Jumlah produk dengan stok rendah
        $lowStock = Product::whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->where('min_stock_quantity', '>', 0)
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Penjualan Bulan Ini', 'Rp ' . number_format($salesThisMonth, 0, ',', '.'))
                ->description('Sales Order dikonfirmasi')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pembelian Bulan Ini', 'Rp ' . number_format($purchaseThisMonth, 0, ',', '.'))
                ->description('Purchase Order aktif')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('SO Menunggu Konfirmasi', $pendingSO)
                ->description('Sales Order berstatus draft')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingSO > 0 ? 'warning' : 'success'),

            Stat::make('Produk Stok Rendah', $lowStock)
                ->description('Di bawah batas minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),
        ];
    }
}
