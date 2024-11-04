<?php

namespace App\Filament\Resources\ReviewerResource\Pages;

use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\ReviewerResource;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Infolists\Components\Actions\Action;
use Filament\Forms\Components\Textarea;

class ViewReviewer extends ViewRecord
{
    protected static string $resource = ReviewerResource::class;

    protected static string $view = 'filament.pages.reviewer';

    public function getTitle(): string | Htmlable
    {
        return __($this->record->title);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Create a comment')
                    ->headerActions([
                        Action::make('comment')->label('Add')
                            ->form([
                                Textarea::make('content')->label('Comment'),
                            ]),
                    ])
                    ->schema([
                        TextEntry::make('content')->label(false)->markdown()->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500)
                    ])
            ]);
    }
}