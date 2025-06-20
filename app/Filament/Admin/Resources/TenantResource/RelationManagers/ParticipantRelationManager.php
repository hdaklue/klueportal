<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TenantResource\RelationManagers;

use App\Actions\Tenant\AddMember;
use App\Actions\Tenant\RemoveMember;
use App\Enums\Role\RoleEnum;
use App\Filament\Admin\Resources\TenantResource;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParticipantRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->recordTitleAttribute('roleable_type')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                TextColumn::make('role')
                    ->getStateUsing(fn ($record) => $record->roles->first()->name),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->label('Add Member')
                    ->icon('heroicon-s-user-plus')
                    ->form(fn (RelationManager $livewire) => TenantResource::getAddMemberSchema($livewire->getOwnerRecord()))
                    ->action(function (RelationManager $livewire, $data) {
                        try {

                            $user = User::where('id', $data['members'])->first();
                            AddMember::run($livewire->getOwnerRecord(), $user, RoleEnum::from($data['system_roles']));
                            Notification::make()
                                ->body('Participant added')
                                ->success()
                                ->color('success')
                                ->send();
                        } catch (\Exception $exception) {
                            Notification::make()
                                ->body('Something went wrong')
                                ->danger()
                                ->color('danger')
                                ->send();
                        }
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()
                    ->label('Remove')
                    ->action(fn (RelationManager $livewire, $record) => RemoveMember::run($livewire->getOwnerRecord(), $record, filament()->auth()->user())),

                // ->after(fn (RelationManager $livewire, $record) => $livewire->getOwnerRecord()->removeParticipant($record)),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
                // Tables\Actions\BulkActionGroup::make([

                //     // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
