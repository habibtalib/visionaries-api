<?php
namespace App\Filament\Resources\IslamicEventResource\Pages;
use App\Filament\Resources\IslamicEventResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
class ListIslamicEvents extends ListRecords {
    protected static string $resource = IslamicEventResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
