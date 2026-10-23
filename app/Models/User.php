<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'avatar',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The staff profile backing this login — carries the branch assignment.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * The branch this login works out of, or null when the roster has none on
     * file. Drives App\Models\Scopes\BranchScope — a null branch sees no
     * visits at all, rather than every branch's.
     */
    public function branchId(): ?int
    {
        return $this->employee?->branch_id;
    }

    /**
     * What the navbar should say about this login's branch.
     */
    public function branchLabel(): string
    {
        if ($branch = $this->employee?->branch) {
            return $branch->display_name;
        }

        // Administration is not stranded without one — it reads every branch —
        // so only a branch-scoped login is told that it has none.
        return $this->canSeeAllBranches() ? 'All branches' : 'No branch assigned';
    }

    /**
     * Administration reads every branch. Everybody else is held to their own.
     */
    public function canSeeAllBranches(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('slug', $roles)->isNotEmpty();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        // System Settings is not reached through the permission table. A
        // permission row would let any Admin quietly widen their own reach, so
        // the Super Admin grants it per role from Settings → Access instead —
        // and everything asking about `settings.*` is answered from there.
        if (str_starts_with($permissionSlug, 'settings.')) {
            return $this->canOpenSettings();
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Roles the Super Admin may let into System Settings, keyed by slug, with
     * the switch that grants each one. Every switch starts off, so the section
     * belongs to Super Admin alone until somebody is deliberately added.
     *
     * @var array<string, string>
     */
    private const SETTINGS_ACCESS = [
        'admin' => 'settings_access_admin',
        'fieldmarketer' => 'settings_access_sales',
    ];

    /**
     * Whether this login may open System Settings.
     */
    public function canOpenSettings(): bool
    {
        $switches = null;

        foreach (self::SETTINGS_ACCESS as $roleSlug => $settingKey) {
            if (! $this->hasRole($roleSlug)) {
                continue;
            }

            // Only read once, and only if this login holds a role that could
            // be granted one — the common case is a field marketer, and that
            // should not cost a query on every sidebar render.
            $switches ??= Setting::query()
                ->whereIn('key', array_values(self::SETTINGS_ACCESS))
                ->pluck('value', 'key');

            if (($switches[$settingKey] ?? 'no') === 'yes') {
                return true;
            }
        }

        return false;
    }

    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role && !$this->roles->contains($role->id)) {
            $this->roles()->attach($role->id);
        }
    }

    public function getRoleNameAttribute(): string
    {
        return $this->roles->first()?->name ?? 'User';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }
        return $initials ?: 'U';
    }
}
