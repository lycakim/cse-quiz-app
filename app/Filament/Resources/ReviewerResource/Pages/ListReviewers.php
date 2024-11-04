<?php

namespace App\Filament\Resources\ReviewerResource\Pages;

use App\Filament\Resources\ReviewerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviewers extends ListRecords
{
    protected static string $resource = ReviewerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
