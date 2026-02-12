<?php
namespace App\Filament\Widgets;

use App\Models\Action;
use App\Models\JournalEntry;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Total Actions', Action::count())
                ->icon('heroicon-o-bolt')
                ->color('success'),
            Stat::make('Journal Entries', JournalEntry::count())
                ->icon('heroicon-o-book-open')
                ->color('info'),
            Stat::make('Active Today', User::whereDate('updated_at', today())->count())
                ->icon('heroicon-o-signal')
                ->color('warning'),
        ];
    }
}
