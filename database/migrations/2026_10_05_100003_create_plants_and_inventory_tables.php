<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Plant Categories
        Schema::create('plant_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Plants Master
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code')->unique();
            $table->string('name');
            $table->string('botanical_name')->nullable();
            $table->string('common_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('plant_categories')->nullOnDelete();
            $table->string('plant_type')->default('Outdoor'); // Indoor, Outdoor, Flowering, Trees, Shrubs, Palms, Climbers, Lawn, etc.
            $table->string('sunlight_requirement')->nullable(); // Full Sun, Partial Shade, Full Shade, Low Light
            $table->string('water_requirement')->nullable(); // Low, Moderate, High, Daily
            $table->string('height')->nullable(); // e.g., 2-3 ft, 4-6 ft
            $table->string('plant_size')->nullable(); // Small, Medium, Large, Extra Large
            $table->string('pot_size')->nullable(); // 6 inch, 8 inch, 10 inch, 12 inch, Grow Bag
            $table->string('unit')->default('Nos'); // Nos, Bag, Pot, Sq.Ft
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->integer('reorder_level')->default(10);
            $table->string('status')->default('active'); // active, inactive
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('plant_type');
            $table->index('status');
        });

        // Inventory Categories
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Warehouses / Nurseries
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('location')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Inventory Items (Plants, Pots, Fertilizers, Soil, Tools, Irrigation Materials, Chemicals)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->string('unit')->default('Nos');
            $table->integer('opening_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('rack')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('sku');
            $table->index('name');
        });

        // Inventory Transactions (Stock In, Stock Out, Site Transfer, Return, Adjustment, Damaged)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('transaction_type'); // Purchase, Stock In, Stock Out, Site Transfer, Return, Adjustment, Damaged, Plant Replacement
            $table->integer('quantity'); // Positive for addition, negative for deduction
            $table->string('reference_type')->nullable(); // Project, Site, PurchaseOrder, Maintenance
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['item_id', 'transaction_type']);
        });

        // Vendors
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('payment_terms')->nullable(); // e.g. Net 15, Net 30, Cash on Delivery
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('po_date');
            $table->date('delivery_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('Draft'); // Draft, Sent, Received, Cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('unit')->default('Nos');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('plants');
        Schema::dropIfExists('plant_categories');
    }
};
