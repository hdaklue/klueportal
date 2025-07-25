<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role\RoleEnum;
use App\Models\Page;
use App\Models\User;

final class PagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Page $page): bool
    {
        return $user->isAssignedTo($page);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->hasAssignmentOn($page, RoleEnum::ADMIN)
        || $user->hasAssignmentOn($page, RoleEnum::MANAGER);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Page $page): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Page $page): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Page $page): bool
    {
        return false;
    }

    public function manageMembers(User $user, Page $page): bool
    {

        return $user->hasAssignmentOn($page, RoleEnum::ADMIN) || $user->hasAssignmentOn($page, RoleEnum::MANAGER);
    }
}
