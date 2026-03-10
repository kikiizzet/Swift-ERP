<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Pelanggan
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable()->comment('NPWP');
            $table->string('currency')->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel Vendor/Supplier
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable()->comment('NPWP');
            $table->string('currency')->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel Kategori Produk
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tabel Produk
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('storable'); // storable, service, consumable
            $table->decimal('sales_price', 18, 2)->default(0);
            $table->decimal('cost_price', 18, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_quantity')->default(0)->comment('Batas minimum stok');
            $table->string('unit')->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel Varian Produk
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Kaos XL Merah"
            $table->string('sku')->unique();
            $table->decimal('extra_price', 18, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->json('attributes')->nullable(); // {"size": "XL", "color": "Merah"}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('customers');
    }
};
