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
        // Customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('customer_type')->default('Individual'); // Individual, Company, Apartment, Villa, etc.
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('country')->default('India');
            $table->string('status')->default('active'); // active, inactive, blacklisted
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
            $table->index('status');
        });

        // Customer Contacts
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Leads table
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('source')->default('Website'); // Website, Google, Referral, Instagram, etc.
            $table->string('interested_service')->nullable(); // Landscaping, Garden Maintenance, Plant Supply, AMC, etc.
            $table->string('status')->default('New'); // New, Contacted, Qualified, Site Visit Required, Quotation Required, Converted, Lost
            $table->string('priority')->default('Medium'); // Low, Medium, High, Urgent
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('expected_value', 12, 2)->default(0);
            $table->date('expected_closing_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('source');
            $table->index('priority');
            $table->index('follow_up_date');
        });

        // Lead Activities (calls, meetings, notes)
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type')->default('Note'); // Call, Meeting, Email, Site Visit, Note, Status Change
            $table->text('notes');
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });

        // Sites / Properties
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_code')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('site_type')->default('Residential'); // Residential, Commercial, Villa, Apartment, Hotel, etc.
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('total_area_sqft', 10, 2)->default(0);
            $table->decimal('built_up_area_sqft', 10, 2)->default(0);
            $table->decimal('landscape_area_sqft', 10, 2)->default(0);
            $table->string('soil_type')->nullable(); // Red Soil, Black Soil, Sandy Loam, Clay, etc.
            $table->string('water_availability')->nullable(); // Borewell, Municipal, Treated/STP, Tanker
            $table->boolean('irrigation_available')->default(false);
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default('active'); // active, inactive, completed
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('site_type');
        });

        // Site Visits
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_code')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('visit_date');
            $table->time('visit_time')->nullable();
            $table->string('purpose')->default('Initial Assessment'); // Initial Assessment, Soil Test, Measurement, Follow-up, Routine
            $table->string('site_area')->nullable();
            $table->text('measurements')->nullable();
            $table->string('soil_condition')->nullable();
            $table->text('existing_plants')->nullable();
            $table->string('water_source')->nullable();
            $table->text('irrigation_details')->nullable();
            $table->text('customer_requirements')->nullable();
            $table->text('recommended_plants')->nullable();
            $table->decimal('estimated_budget', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->string('next_action')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->string('status')->default('Scheduled'); // Scheduled, Completed, Rescheduled, Cancelled
            $table->timestamps();

            $table->index('visit_date');
            $table->index('status');
        });

        // Site Visit Photos
        Schema::create('site_visit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_visit_photos');
        Schema::dropIfExists('site_visits');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customers');
    }
};
