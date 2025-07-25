<?php

declare(strict_types=1);
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\PortalPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    PortalPanelProvider::class,

];
