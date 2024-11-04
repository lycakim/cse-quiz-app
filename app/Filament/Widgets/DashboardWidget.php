<?php

namespace App\Filament\Widgets;

use App\Models\Progress;
use App\Models\Question;
use App\Models\Section;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;

class DashboardWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
            Stat::make('Total Sections', Section::count()),
            Stat::make('Total Responses', Progress::count()),
            Stat::make('Total Questions', Question::count()),
        ];
    }
}