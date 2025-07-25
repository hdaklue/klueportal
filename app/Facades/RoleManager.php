<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

final class RoleManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'role.manager';
    }
}
