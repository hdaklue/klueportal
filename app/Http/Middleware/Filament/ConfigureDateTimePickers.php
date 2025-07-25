<?php

declare(strict_types=1);

namespace App\Http\Middleware\Filament;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureDateTimePickers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = filamentUser()->timezone ?: \config('app.timezone');

        DateTimePicker::configureUsing(function (DateTimePicker $component) use ($timezone) {
            $component->timezone($timezone);
            $component->format('m/d/Y g:i A');

            return $component;
        });
        DatePicker::configureUsing(fn (DatePicker $component) => $component->timezone($timezone),
        );

        TextColumn::configureUsing(fn (TextColumn $component) => $component->timezone($timezone),
        );

        return $next($request);
    }
}
