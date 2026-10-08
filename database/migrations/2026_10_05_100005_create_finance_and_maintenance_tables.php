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
        // Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('designation'); // Gardener, Supervisor, Landscape Designer, Project Manager, Labour, Driver, etc.
            $table->string('department')->default('Operations'); // Operations, Sales, Horticulture, Accounts, Maintenance
            $table->date('joining_date')->nullable();
            $table->string('employee_type')->default('Full-Time'); // Full-Time, Contract, Daily Wage
            $table->decimal('salary', 10, 2)->default(0);
            $table->string('status')->default('active'); // active, inactive
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Attendance
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('Present'); // Present, Absent, Half-Day, Leave
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // Maintenance Contracts (AMC)
        Schema::create('maintenance_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('amc_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('contract_value', 12, 2);
            $table->string('billing_frequency')->default('Monthly'); // Monthly, Quarterly, Half-Yearly, Annually
            $table->string('service_frequency')->default('Weekly'); // Daily, Weekly, Biweekly, Monthly, Quarterly
            $table->foreignId('assigned_team_lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('scope_of_work')->nullable();
            $table->string('status')->default('Active'); // Active, Expiring Soon, Expired, Terminated
            $table->date('renewal_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('end_date');
        });

        // Maintenance Schedules
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('maintenance_contracts')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->string('status')->default('Scheduled'); // Scheduled, In Progress, Completed, Missed
            $table->foreignId('assigned_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Maintenance Records (Logs with activities and Before/After photos)
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained('maintenance_schedules')->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type'); // Watering, Pruning, Trimming, Fertilization, Pest Control, Weeding, Lawn Cutting, Soil Treatment, Plant Replacement, Cleaning, Irrigation Maintenance
            $table->string('area_details')->nullable();
            $table->text('work_description')->nullable();
            $table->string('before_photo')->nullable();
            $table->string('after_photo')->nullable();
            $table->boolean('customer_approval')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid, overdue
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_status');
            $table->index('invoice_date');
        });

        // Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('Nos');
            $table->decimal('rate', 10, 2);
            $table->decimal('tax_percent', 5, 2)->default(18);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('Bank Transfer'); // Cash, Bank Transfer, UPI, Cheque, Card, Other
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('payment_date');
        });

        // Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('category'); // Labour, Transport, Plants, Materials, Fuel, Equipment, Maintenance, Other
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('Approved'); // Pending, Approved, Rejected
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('Medium'); // Low, Medium, High, Urgent
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('Open'); // Open, Assigned, In Progress, Resolved, Closed
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        // Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('documentable'); // Customer, Site, Project, Quotation, WorkOrder, AMC
            $table->string('title');
            $table->string('category')->default('General'); // Agreement, Plan, Estimate, Photo, Invoice, Receipt, Other
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('maintenance_contracts');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
