<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Pergerakan Stok
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('type'); // in, out, adjustment
            $table->string('reference_type')->nullable(); // App\Models\SalesOrder
            $table->unsignedBigInteger('reference_id')->nullable(); // ID transaksi sumber
            $table->decimal('quantity', 18, 4);
            $table->decimal('quantity_before', 18, 4);
            $table->decimal('quantity_after', 18, 4);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->string('warehouse')->default('Gudang Utama');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // Tabel Penyesuaian Inventaris (Opname)
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // ADJ-2024-0001
            $table->string('status')->default('draft'); // draft, validated
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('quantity_theoretical', 18, 4); // Stok di sistem
            $table->decimal('quantity_counted', 18, 4);     // Stok fisik
            $table->decimal('quantity_difference', 18, 4)->default(0); // dihitung: counted - theoretical
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('stock_movements');
    }
};
