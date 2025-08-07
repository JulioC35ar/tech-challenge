<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\CommentResource\RelationManagers;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
       return $form->schema([
            Forms\Components\Select::make('maintenance_order_id')
                ->label('Order')
                ->relationship('maintenanceOrder', 'title')
                ->disabled()
                ->required(),

            Forms\Components\Select::make('user_id')
                ->label('Author')
                ->relationship('user', 'name')
                ->disabled()
                ->required(),

            Forms\Components\Textarea::make('body')
                ->label('Comment')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
            return $table
                ->columns([
                    Tables\Columns\TextColumn::make('maintenanceOrder.title')
                        ->label('Order Title')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('user.name')
                        ->label('Author')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('body')
                        ->label('Comment')
                        ->wrap()
                        ->limit(80)
                        ->toggleable(),

                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    Tables\Columns\TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->filters([
                    //
                ])
                ->actions([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\EditAction::make()
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
            'index' => Pages\ListComments::route('/'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
