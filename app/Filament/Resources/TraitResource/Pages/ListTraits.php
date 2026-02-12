<?php
namespace App\Filament\Resources\TraitResource\Pages;
use App\Filament\Resources\TraitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListTraits extends ListRecords {
    protected static string $resource = TraitResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
