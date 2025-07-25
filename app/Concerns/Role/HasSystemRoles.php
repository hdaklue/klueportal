<?php

declare(strict_types=1);

namespace App\Concerns\Role;

use App\Enums\Role\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasSystemRoles
{
    public function systemRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'tenant_id');
    }

    public function getSystemRoles(): Collection
    {
        return $this->systemRoles()->get();
    }

    public function systemRoleByName(string|RoleEnum $role): Role
    {
        if ($role instanceof RoleEnum) {
            $role = $role->value;
        }

        // @phpstan-ignore return.type
        return $this->systemRoles()->where('name', $role)->firstOrFail();
    }
}
