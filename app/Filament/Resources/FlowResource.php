<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\FlowStatus;
use App\Filament\Resources\FlowResource\Pages;
use App\Models\Flow;
use App\Services\Flow\TimeProgressService;
use App\Tables\Columns\Progress;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FlowResource extends Resource
{
    protected static ?string $model = Flow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        $flowProgressService = app(TimeProgressService::class);

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $isAdmin = filament()->getTenant()->isAdmin(\filament()->auth()->user());
                $query->unless($isAdmin, function ($query) {
                    $query->forParticipant(\filament()->auth()->user());
                })->running()->with(['creator', 'participants'])->ordered();
            })
            ->deferLoading()
            ->reorderable('order_column')
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('status')
                    ->getStateUsing(fn ($record) => FlowStatus::from($record->status)->getLabel())
                    ->color(fn ($record) => FlowStatus::from($record->status)->getColor())
                    ->badge(),
                ImageColumn::make('creator')
                    ->getStateUsing(fn ($record) => filament()->getUserAvatarUrl($record->creator))
                    ->size(30)
                    ->circular(),

                // SelectColumn::make('status')
                //     ->options(FlowStatus::class),

                Progress::make('time_progress')
                    ->getStateUsing(fn ($record) => $flowProgressService->getProgressDetails($record)),
                TextColumn::make('start_date')
                    ->since(),
                TextColumn::make('due_date')
                    ->since(),
                TextColumn::make('days_left')
                    ->getStateUsing(fn ($record) => $flowProgressService->getDaysRemaining($record)),
                TextColumn::make('duration')
                    ->getStateUsing(fn ($record) => $flowProgressService->getTotalDays($record)),

                ImageColumn::make('members')
                    ->getStateUsing(fn ($record) => $record->participants->pluck('avatar'))
                    ->circular()
                    ->stacked(),

            ])

            ->filters([
                SelectFilter::make('status')
                    ->options(FlowStatus::class),
            ], FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlows::route('/'),
            'create' => Pages\CreateFlow::route('/create'),
            // 'edit' => Pages\EditFlow::route('/{record}/edit'),
        ];
    }
}
