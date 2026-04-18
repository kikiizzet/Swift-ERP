<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/invoices/{invoice}/print', function (\App\Models\Invoice $invoice) {
    $invoice->load(['customer', 'salesOrder.items.product']);
    $company = \App\Models\Company::first();
    
    return view('invoices.print', compact('invoice', 'company'));
})->name('admin.invoices.print')->middleware(['web', 'auth']);
