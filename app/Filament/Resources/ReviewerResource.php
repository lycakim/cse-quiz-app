<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Reviewer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ReviewerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ReviewerResource\RelationManagers;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;

class ReviewerResource extends Resource
{
    protected static ?string $model = Reviewer::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->visible()
                            ->required()
                            ->maxLength(255),
                        MarkdownEditor::make('content')
                            ->toolbarButtons([
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'heading',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'table',
                                'undo',
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('title')->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn ($record) => auth()->user()->isAdmin()),
                Tables\Actions\DeleteAction::make()->visible(fn ($record) => auth()->user()->isAdmin())
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListReviewers::route('/'),
            'create' => Pages\CreateReviewer::route('/create'),
            'edit' => Pages\EditReviewer::route('/{record}/edit'),
            'view' => Pages\ViewReviewer::route('/{record}/view'),
        ];
    }
}