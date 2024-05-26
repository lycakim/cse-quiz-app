<?php

namespace App\Filament\Pages;

use App\Models\Progress as ModelProgress;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class Progress extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.pages.progress';

    protected static ?int $navigationSort = 2;

    public Collection $progress;

    public function table(Table $table): Table
    {
        return $table
            ->query(ModelProgress::query()->latest())
            ->columns([
                TextColumn::make('user.name')->label('Taker')->searchable(),
                TextColumn::make('section.name')->label('Questionnaire')->searchable(),
                TextColumn::make('score')->label('Score')->searchable(),
                TextColumn::make('created_at')->label(false)->formatStateUsing(fn($state) => $state->format('M d, Y g:i a'))->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}