<?php

namespace App\Filament\Resources\ReviewerResource\Pages;

use App\Filament\Resources\ReviewerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReviewer extends EditRecord
{
    protected static string $resource = ReviewerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}