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
                    ->searchable()
                    ->visible(fn () => Auth::user()?->isSupervisor()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->tooltip('View maintenance order'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->tooltip('Edit maintenance order'),

                    Tables\Actions\Action::make('Start')
                        ->label('Start Work')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->tooltip('Mark order as In Progress')
                        ->visible(fn ($record) => auth()->user()->isTechnician() && $record->status === 'created')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->update(['status' => 'in_progress'])),

                    Tables\Actions\Action::make('Finish')
                        ->label('Mark as Pending Approval')
                        ->icon('heroicon-o-flag')
                        ->tooltip('Finish and send for approval')
                        ->visible(fn ($record) => auth()->user()->isTechnician() && $record->status === 'in_progress')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->update(['status' => 'pending_approval'])),

                    Tables\Actions\Action::make('Approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->tooltip('Approve this order')
                        ->visible(fn ($record) => auth()->user()->isSupervisor() && $record->status === 'pending_approval')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->update(['status' => 'approved'])),

                    Tables\Actions\Action::make('Reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->tooltip('Reject with reason')
                        ->visible(fn ($record) => auth()->user()->isSupervisor() && $record->status === 'pending_approval')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Reason for Rejection')
                                ->required(),
                        ])
                        ->action(fn ($record, array $data) => $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ])),

                    Tables\Actions\Action::make('view_comments')
                        ->label('View Comments')
                        ->icon('heroicon-o-chat-bubble-left')
                        ->tooltip('View comments for this order')
                        ->modalHeading('Comments')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(fn ($record) => view('filament.modals.view-comments', [
                            'record' => $record,
                            'comments' => $record->comments()->latest()->get(),
                        ])),

                    Tables\Actions\Action::make('add_comment')
                        ->label('Add Comment')
                        ->icon('heroicon-o-plus-circle')
                        ->modalHeading('Add Comment')
                        ->form([
                            Forms\Components\Textarea::make('body')
                                ->label('Comment')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function ($record, array $data) {
                            $record->comments()->create([
                                'body' => $data['body'],
                                'user_id' => auth()->id(),
                            ]);
                        })
                        ->visible(fn () => auth()->check()),
                ]),
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
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user->isTechnician() && ! request()->has('tableFilters')) {
            $query->where('technician_id', $user->id)
                ->orderByRaw("
                    CASE priority
                        WHEN 'high' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'low' THEN 3
                        ELSE 4
                    END
                ");
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
