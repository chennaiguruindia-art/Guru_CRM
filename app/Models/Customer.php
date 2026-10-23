<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'customer_type',
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'whatsapp',
        'gst_number',
        'pan',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'status',
        'branch_id',
        'created_by_id',
        'assigned_to_id',
        'notes',
    ];

    /**
     * A CRM record is read by the Super Admin, by the Admin of its own branch,
     * and by whoever entered it — nobody else.
     *
     * @see \App\Models\Scopes\RecordVisibilityScope
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\RecordVisibilityScope());
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function maintenanceContracts(): HasMany
    {
        return $this->hasMany(MaintenanceContract::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * The next Client code for the whole company.
     *
     * Counted across every client rather than through the scope that decides
     * who may read them — the same reason as Lead::generateCode(): a viewer
     * who can see none of them would be handed a code somebody else owns.
     */
    public static function generateCode(): string
    {
        $last = static::withoutGlobalScopes()->withTrashed()->latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'CUST-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
