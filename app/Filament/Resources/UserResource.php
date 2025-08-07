<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\{TextInput, Select};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->required()
                ->email()
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->password()
                ->required(fn ($context) => $context === 'create')
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->label('Password'),

            Select::make('role')
                ->label('Role')
                ->options(collect(UserRole::cases())->mapWithKeys(fn ($role) => [
                    $role->value => ucfirst($role->value),
                ]))
                ->required()
                ->default(UserRole::Technician->value)
                ->dehydrateStateUsing(fn ($state) => UserRole::from($state))
                ->afterStateHydrated(fn ($component, $state) => $component->state($state?->value ?? $state))
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state->value))
                    ->color(fn ($state) => match ($state->value) {
                        'supervisor' => 'success',
                        'technician' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make()
                        ->visible(fn () => auth()->user()?->isSupervisor()),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => auth()->user()?->isSupervisor()),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isSupervisor() ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isSupervisor() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->isSupervisor() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->isSupervisor() ?? false;
    }

    // public static function canViewAny(): bool
    // {
    //     return Auth::user()?->isSupervisor();
    // }
}
