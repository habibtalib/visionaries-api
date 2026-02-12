<?php
namespace App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
class ListQuizzes extends ListRecords {
    protected static string $resource = QuizResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
