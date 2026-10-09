<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The entry points in the sidebar under CRM & Clients. They are all the
     * same Visit records — the channel only decides which list they appear
     * under and what gets stamped into `source`, which is no longer typed by
     * the user. Cold Call is the catch-all: anything filed without a channel
     * lands there, and its list shows every Visit regardless of source.
     */
    public const CHANNELS = [
        'cold_call'       => 'Cold Call',
        'promotion_email' => 'Promotion Email',
        'existing_client' => 'Existing Client',
    ];

    /** Source stamped when a Visit is created without a known channel. */
    public const DEFAULT_SOURCE = 'Cold Call';

    /**
     * key => [label, route name, permission that guards its list].
     *
     * Read by the list, create and detail views and by LeadController, so the
     * three never drift apart — and so nobody is handed a breadcrumb into a
     * list their own permission would refuse.
     */
    public const CHANNEL_LISTS = [
        'cold_call'       => ['Cold Calls / Direct Call', 'cold_calls.index', 'leads.view'],
        'promotion_email' => ['Promotion Email / Call', 'promotion_emails.index', 'leads.promotion'],
        'existing_client' => ['Existing Client Visit', 'client_visits.index', 'leads.existing_client'],
    ];

    /** Engagement type — decides what conversion creates. */
    public const SERVICE_TYPES = ['AMC', 'Single Project'];

    /** Options offered for Purpose of Visit. */
    public const PURPOSES = [
        'New Enquiry',
        'Follow-up',
        'Site Measurement',
        'Quotation Submission',
        'Contract Renewal',
        'Complaint Resolution',
        'Payment Collection',
        'Other',
    ];

    /**
     * Whether this Visit is against a brand new prospect or somebody already
     * in Clients. Optional, and independent of the channel it was filed under
     * — a Cold Call can still turn out to be a returning client.
     */
    public const CLIENT_TYPES = ['New Client', 'Existing Client'];

    protected $fillable = [
        'lead_code',
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'whatsapp',
        'address',
        'city',
        'state',
        'pincode',
        'source',
        'client_type',
        'purpose_of_visit',
        'service_type',
        'interested_service',
        'status',
        'priority',
        'assigned_to_id',
        'created_by_id',
        'converted_customer_id',
        'expected_value',
        'expected_closing_date',
        'follow_up_date',
        'remarks',
        'visiting_card_photo',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'expected_closing_date' => 'date',
        'follow_up_date' => 'date',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public static function generateCode(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'LEAD-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'New' => 'bg-info text-dark',
            'Contacted' => 'bg-primary text-white',
            'Qualified' => 'bg-secondary text-white',
            'Site Visit Required' => 'bg-warning text-dark',
            'Quotation Required' => 'bg-purple text-white',
            'Converted' => 'bg-success text-white',
            'Lost' => 'bg-danger text-white',
            default => 'bg-light text-dark'
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'Urgent' => 'bg-danger text-white',
            'High' => 'bg-warning text-dark',
            'Medium' => 'bg-info text-dark',
            'Low' => 'bg-secondary text-white',
            default => 'bg-light text-dark'
        };
    }
}
