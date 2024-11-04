<?php

namespace App\Filament\Resources\SectionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class QuestionRelationManager extends RelationManager
{
    protected static string $relationship = 'question';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->columnSpanFull()
                    ->autocomplete(false)
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                
                Forms\Components\FileUpload::make('image')
                    ->columnSpanFull()
                    ->image()
                    ->disk('public') 
                    ->directory('images')
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return uniqid() . '.'. $file->getClientOriginalExtension();
                    })
                    ->label('Image'),

                Forms\Components\Repeater::make('options')->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\Checkbox::make('correct'),
                    ])
                    ->columnSpanFull()
                    ->defaultItems(5)
                    ->reorderable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('section_id')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->modalHeading('Create Question'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}