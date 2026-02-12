<?php
namespace App\Filament\Resources\ReelResource\Pages;
use App\Filament\Resources\ReelResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
class ListReels extends ListRecords {
    protected static string $resource = ReelResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
