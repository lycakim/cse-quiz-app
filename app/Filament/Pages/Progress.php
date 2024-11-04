<?php

namespace App\Filament\Pages;

use App\Models\Progress as ModelProgress;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Filament\Tables\Concerns\InteractsWithTable;

class Progress extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.pages.progress';

    protected static ?int $navigationSort = 4;

    public Collection $progress;

    public static function getNavigationBadge(): ?string
    {
        return ModelProgress::count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ModelProgress::query()->latest())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Taker')
                    ->searchable(),
                TextColumn::make('section.name')
                    ->label('Questionnaire')
                    ->searchable(),
                TextColumn::make('score')
                    ->label('Score')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->formatStateUsing(fn($state) => $state->format('M d, Y g:i A'))
                    ->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('show_response')
                    ->visible(fn ($record) => auth()->user()->isAdmin() || $record->user == auth()->user())
                    ->modalHeading(fn ($record) => $record->section['name'] .' ('. $record['score'].')')
                    ->modalContent(fn ($record): View => view(
                        'modal-responses',
                        ['record' => $record],
                    ))
                    ->action(function (array $data) {
                        // ...
                    })
                    ->icon('heroicon-o-document-text')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([
                // ...
            ]);
    }
}