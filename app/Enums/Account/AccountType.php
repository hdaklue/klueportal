<?php

declare(strict_types=1);

namespace App\Enums\Account;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountType: string implements HasColor, HasLabel
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case USER = 'user';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::USER => 'User',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ADMIN => 'primary',
            self::MANAGER => 'warning',
            self::USER => 'gray',
        };
    }
}
