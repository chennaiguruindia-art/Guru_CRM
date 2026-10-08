<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function givePermissionTo(...$permissions): self
    {
        $permissions = collect($permissions)->flatten()->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('slug', $permission)->firstOrFail();
            }
            return $permission;
        });

        $this->permissions()->syncWithoutDetaching($permissions->pluck('id'));

        return $this;
    }
}
