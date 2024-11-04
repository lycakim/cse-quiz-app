<?php

namespace App\Filament\Resources\ReviewerResource\Pages;

use App\Filament\Resources\ReviewerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReviewer extends CreateRecord
{
    protected static string $resource = ReviewerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}