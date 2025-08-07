<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentsResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\MaintenanceOrderResource\Pages;
use App\Filament\Resources\MaintenanceOrderResource\RelationManagers;
use App\Models\MaintenanceOrder;
use Filament\Forms;
use Filament\Forms\Components\{Select, TextInput, Textarea};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MaintenanceOrderResource extends Resource
{
    protected static ?string $model = MaintenanceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->disabled($user->isTechnician()),

                Select::make('asset_id')
                    ->relationship('asset', 'name')
                    ->required()
                    ->disabled($user->isTechnician()),

                Select::make('priority')
                    ->options([
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    ->default('medium')
                    ->required()
                    ->disabled($user->isTechnician()),

                Select::make('technician_id')
                    ->relationship('technician', 'name')
                    ->label('Assigned Technician')
                    ->searchable()
                    ->nullable()
                    ->disabled($user->isTechnician()),

                Select::make('status')
                    ->options([
                        'created' => 'Created',
                        'in_progress' => 'In Progress',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('created')
                    ->disabled()
                    ->dehydrated(),

                Textarea::make('rejection_reason')
                    ->rows(3)
                    ->label('Rejection Reason')
                    ->visible(fn ($record) => $record?->status === 'rejected'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('asset.name')->label('Asset'),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'secondary',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'gray',
                        'in_progress' => 'info',
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('technician.name')->label('Technician'),
                TextColumn::make('updated_at')->dateTime()->label('Updated'),
            ])
            ->defaultSort('priority', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'created' => 'Created',
                        'in_progress' => 'In Progress',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Status'),

                SelectFilter::make('priority')
                    ->options([
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    ->label('Priority'),

                SelectFilter::make('technician_id')
                    ->relationship('technician', 'name')
                    ->label('Technician')
                    ->searchable(),

                TernaryFilter::make('my_orders')
                    ->label('My Orders')
                    ->placeholder('All Orders')
                    ->trueLabel('Only Mine')
                    ->falseLabel('Exclude Mine')
                    ->query(function (Builder $query, $state) {
                        if ($state === true) {
                            return $query->where('technician_id', auth()->id());
                        } elseif ($state === false) {
                            return $query->where('technician_id', '!=', auth()->id());
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Iniciar orden
                Tables\Actions\Action::make('Start')
                    ->label('Start Work')
                    ->visible(fn ($record): bool => auth()->user()->isTechnician() &&  $record->status === 'created')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'in_progress'])),

                // Marcar como pendiente aprobación
                Tables\Actions\Action::make('Finish')
                    ->label('Mark as Pending Approval') ->visible(fn ($record): bool => auth()->user()->isTechnician() && $record->status === 'in_progress')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'pending_approval'])),

                // Aprobar
                Tables\Actions\Action::make('Approve')
                    ->visible(fn ($record): bool => auth()->user()->isSupervisor() && $record->status === 'pending_approval')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'approved'])),

                // Rechazar
                Tables\Actions\Action::make('Reject')->visible(fn ($record): bool => auth()->user()->isSupervisor() && $record->status === 'pending_approval')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('Reason for Rejection'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),

                // Comments
                Tables\Actions\Action::make('view_comments')
                    ->label('View Comments')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->modalHeading('Comments')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function ($record) {
                        return view('filament.modals.view-comments', [
                            'record' => $record,
                            'comments' => $record->comments()->latest()->get(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceOrders::route('/'),
            'create' => Pages\CreateMaintenanceOrder::route('/create'),
            'edit' => Pages\EditMaintenanceOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if ($user->role === 'technician') {
            // Ver solo las órdenes asignadas a él
            $query->where('technician_id', $user->id)
                ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isSupervisor() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->isSupervisor();
    }


}
