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
        // Estimations
        Schema::create('estimations', function (Blueprint $table) {
            $table->id();
            $table->string('estimation_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('date');
            $table->decimal('plants_total', 12, 2)->default(0);
            $table->decimal('materials_total', 12, 2)->default(0);
            $table->decimal('labour_total', 12, 2)->default(0);
            $table->decimal('transport_total', 12, 2)->default(0);
            $table->decimal('other_total', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('profit_margin_percent', 5, 2)->default(0);
            $table->decimal('profit_margin_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(18);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('status')->default('Draft'); // Draft, Converted to Quotation, Archived
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Estimation Items
        Schema::create('estimation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimation_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // Plant, Material, Labour, Transport, Other
            $table->string('item_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('Nos');
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Quotations
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('estimation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->date('valid_until')->nullable();
            $table->foreignId('sales_person_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('discount_type')->default('fixed'); // fixed, percentage
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(18);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Draft'); // Draft, Sent, Under Review, Approved, Rejected, Expired
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('date');
        });

        // Quotation Items
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('item_type')->default('Plant'); // Plant, Material, Labour, Service, Other
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('Nos');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(18);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
        });

        // Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('contract_value', 12, 2)->default(0);
            $table->decimal('budget', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->string('status')->default('Planning'); // Planning, Approved, In Progress, On Hold, Completed, Cancelled
            $table->integer('progress_percent')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // Project Tasks
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority')->default('Medium'); // Low, Medium, High, Urgent
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('Pending'); // Pending, In Progress, Completed, Cancelled
            $table->timestamps();

            $table->index('status');
        });

        // Project Plants Allocation
        Schema::create('project_plants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->integer('quantity_planned');
            $table->integer('quantity_allocated')->default(0);
            $table->string('unit')->default('Nos');
            $table->string('status')->default('Planned'); // Planned, Dispatched, Planted
            $table->timestamps();
        });

        // Project Materials
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('Nos');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 12, 2);
            $table->timestamps();
        });

        // Work Orders
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->string('status')->default('Draft'); // Draft, Approved, Assigned, In Progress, Completed, Cancelled
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // Work Order Items
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('Nos');
            $table->string('status')->default('Pending'); // Pending, In Progress, Completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('project_materials');
        Schema::dropIfExists('project_plants');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('estimation_items');
        Schema::dropIfExists('estimations');
    }
};
