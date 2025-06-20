<?php

namespace App\Policies;

use App\Enums\Role\RoleEnum;
use App\Models\Flow;
use App\Models\User;

class FlowPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {

        if (filament()->getTenant()) {

            return filament()->getTenant()->isParticipant($user);
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Flow $flow): bool
    {

        return $user->hasAnyRoleOn($flow)
        || $flow->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (filament()->getTenant()) {

            return $user->hasRoleOn([RoleEnum::ADMIN, RoleEnum::TENANT_ADMIN, RoleEnum::SUPER_ADMIN], filament()->getTenant());
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Flow $flow): bool
    {
        return $user->hasRoleOn('writer', $flow) || $user->hasRoleOn(RoleEnum::SUPER_ADMIN, filament()->getTenant());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Flow $flow): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Flow $flow): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Flow $flow): bool
    {
        return true;
    }
}
