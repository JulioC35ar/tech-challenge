<?php

namespace App\Filament\Resources\CommentsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('body')
                ->label('Comment')
                ->required()
                ->rows(4),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Author'),
                TextColumn::make('body')->label('Comment')->wrap(),
                TextColumn::make('created_at')->label('Created')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id(); // asigna el autor automáticamente
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn () => Auth::user()?->isSupervisor()),
                Tables\Actions\DeleteAction::make()->visible(fn () => Auth::user()?->isSupervisor()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
