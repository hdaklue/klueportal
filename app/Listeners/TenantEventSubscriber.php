<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Tenant\MemberRemoved;
use App\Events\Tenant\TanantMemberAdded;
use App\Events\Tenant\TenantCreated;
use App\Facades\RoleManager;
use App\Models\User;
use App\Notifications\Participant\AssignedToEntity;
use App\Notifications\Participant\RemovedFromEntity;
use App\Notifications\TenantCreatedNotification;
use Illuminate\Events\Dispatcher;

final class TenantEventSubscriber
{
    /**
     * Handle the Created event.
     */
    public function handleTenantCreated(TenantCreated $event): void
    {

        $users = User::appAdmin()
            ->where('id', '!=', $event->user->id)
            ->get();

        foreach ($users as $user) {
            $user->notify(new TenantCreatedNotification($event->tenant));
        }

    }

    public function handleMemberRemoved(MemberRemoved $event): void
    {
        RoleManager::clearCache($event->tenant);
        logger("cache should be cleared for {$event->tenant->id}");
        $event->memberRemoved->notify(new RemovedFromEntity($event->tenant));
    }

    public function handleMemberAdded(TanantMemberAdded $event): void
    {

        RoleManager::clearCache($event->tenant);
        $event->user->notify(new AssignedToEntity($event->tenant, $event->role->getLabel()));

    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantCreated::class => 'handleTenantCreated',
            MemberRemoved::class => 'handleMemberRemoved',
            TanantMemberAdded::class => 'handleMemberAdded',
        ];
    }
}
